<?php
namespace Beatom\DataSeo;

use Illuminate\Support\Facades\DB;

/**
 * Class DataSeo
 * @package Beatom\DataSeo
 */
class DataSeo {

    const LOG_TABLE = 'log_data_seo';

    protected $apiUrl = 'https://api.dataforseo.com/';
    protected $client;
    protected $domainsClient;
    protected $excludeTargetsClient;
    protected $excludeTargets;

    protected $consolePrint;

    public $result = [];


    public function __construct( string $domains, $excludeTargets = null){

        require_once 'RestClient/RestClient.php';

        $this->domainsClient = $this->convertParam($domains, true);
        $this->excludeTargets = $excludeTargets;
        $this->excludeTargetsClient = $this->convertParam($excludeTargets);

        $this->client = new \RestClient(
            $this->apiUrl,
            null,
            config('dataseo.login'),
            config('dataseo.password')
        );

        $this->consolePrint = new ConsolePrint();
    }

    /**
     * build param fo API
     * @param string|null $param
     * @param bool $setKey
     * @return array
     */
    private function convertParam($param, $setKey = false){
        $out = [];

        if(!$param){
            return $param;
        }

        $param = str_replace(' ', '', $param);
        $param = explode(',', $param);

        if($setKey){
            for ($i=0; $i < count($param); $i++){
                $out[$i+1] = $param[$i];
            }
        }
        else{
            $out = $param;
        }

        return $out;
    }

    /**
     * Cartesian Product build rows
     * @return array|bool
     */
    private function buildCartesianProduct(){
        if(!$this->excludeTargetsClient){
            return false;
        }

        if(empty($this->result['tasks'][0]['result'][0]['items'])) {
            return false;
        }

        $rows = [];
        foreach ($this->excludeTargetsClient as $target_domain){
            foreach ($this->result['tasks'][0]['result'][0]['items'] as $items){
                foreach ($items as $key => $item) {
                    $row = [];
                    foreach (ConsolePrint::configTable as $col => $lengh) {

                        switch ($col) {
                            case 'excluded_target':
                                $row['excluded_target'] = '';
                                break;
                            case 'target_domain':
                                $row['target_domain'] = $target_domain;
                                break;
                            case 'referring_domain':
                                $row['target'] = $item['target'] ?? '';
                                break;
                            case 'rank':
                                $row['rank'] =  ($item['rank'] ?? 0);
                                break;
                            case 'first_seen':
                            case 'lost_date':
                            $row[$col] = $item[$col] ?? '';
                                break;
                        }
                    }
                    $rows[] = $row;
                }
            }
        }


        return $rows;
    }

    /**
     * @throws \Exception
     */
    public function getDataSeo() {

        if(empty($this->domainsClient)){
            throw new \Exception('Target empty');
        }

        $postData = [
            "targets" => $this->domainsClient,
            "limit" => 10,
            "include_subdomains" => false,
            "exclude_internal_backlinks" => true,
            "order_by" => [
                "1.rank,desc"
            ]
        ];
        if(!empty($this->excludeTargetsClient)){
            $postData['exclude_targets'] = $this->excludeTargetsClient;
        }

        $postArray = [];
        $postArray[] = $postData;

        try {
            $this->result = $this->client->post('/v3/backlinks/domain_intersection/live', $postArray);

            $this->printConsole();


            $this->insertDB();

        } catch (\RestClientException $e) {
            echo PHP_EOL;
            echo "HTTP code: ".$e->getHttpCode().PHP_EOL;
            echo "Error code: ".$e->getCode().PHP_EOL;
            echo "Message: ".$e->getMessage().PHP_EOL;
            echo PHP_EOL;
        }

    }

    /**
     * print table in console
     */
    private function printConsole(){


        $out = PHP_EOL;

        $out .= 'status_code = '.( $this->result['tasks'][0]['status_code'] ?? 'unknown' ).PHP_EOL;
        $out .= 'status_message = '.( $this->result['tasks'][0]['status_message'] ?? 'unknown' ).PHP_EOL;

        if($this->result['tasks'][0]['status_code'] != 20000 ){
            echo $out;
            return;
        }

        $out .= $this->consolePrint->printDivider('=');
        $out .= $this->consolePrint->printHeader();
        $out .= $this->consolePrint->printDivider('=');

        if(!empty($this->result['tasks'][0]['result'][0]['items'])) {
            foreach ($this->result['tasks'][0]['result'][0]['items'] as $items) {
                foreach ($items as $key => $item) {

                    $out.= $this->consolePrint->printRows(
                        $item,
                        $this->excludeTargets,
                        $this->domainsClient[$key]

                    );
                }
            }
        }

        if($rows = $this->buildCartesianProduct()){

            foreach ($rows as $row){
                $out.= $this->consolePrint->printRows(
                    $row,
                    ''
                );
            }
        }

        echo $out;
    }



    private function insertDB(){
        DB::table(self::LOG_TABLE)->insert($this->consolePrint->getInsertDB());
    }


}
