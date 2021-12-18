<?php
namespace Beatom\DataSeo;

use Illuminate\Support\Facades\DB;

/**
 * Class DataSeo
 * @package Beatom\DataSeo
 */
class DataSeo {

    const configTable = [
            'excluded_target' => 30,
            'target_domain' => 30,
            'referring_domain' => 30,
            'rank' => 7,
            'first_seen' => 30,
            'lost_date' => 30,
        ];
    const lenghTable = 162;
    const LOG_TABLE = 'log_data_seo';


    protected $apiUrl = 'https://api.dataforseo.com/';
    protected $client;
    protected $domainsClient;
    protected $excludeTargetsClient;

    protected $domains;
    protected $excludeTargets;

    public $result = [];


    public function __construct( string $domains, $excludeTargets = null){

        require_once 'RestClient/RestClient.php';

        $this->domains = $domains;
        $this->domainsClient = $this->convertParam($domains, true);
        $this->excludeTargets = $excludeTargets;
        $this->excludeTargetsClient = $this->convertParam($excludeTargets);

        $this->client = new \RestClient(
            $this->apiUrl,
            null,
            config('dataseo.login'),
            config('dataseo.password')
        );
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

        $out .= $this->printDivider('=');

        // table header
        $out .= '     excluded_target          |';
        $out .= '       target_domain          |';
        $out .= '       referring_domain       |';
        $out .= ' rank  |';
        $out .= '          first_seen          |';
        $out .= '          lost_date           |';
        $out .= PHP_EOL;

        $out .= $this->printDivider('=');

        $dbLogs = [];
        $date = date('Y-m-d H:i:s');
        foreach ($this->result['tasks'][0]['result'][0]['items'] as $items){
            foreach ($items as $key => $item){

                $log = [];
                foreach (self::configTable as $col=>$lengh){
                    switch ($col){
                        case 'excluded_target':
                            $text = '';
                            if($this->excludeTargets){
                                $text = $this->excludeTargets;
                            }
                            $log['excluded_target'] = $text;
                            $out .= $this->printColum($text, $lengh);
                            break;
                        case 'target_domain':
                            $text = $this->domainsClient[$key] ?? '';
                            $log['target_domain'] = $text;
                            $out .= $this->printColum($text, $lengh);
                            break;
                        case 'referring_domain':
                            $text = $item['target'] ?? '';
                            $log['referring_domain'] = $text;
                            $out .= $this->printColum($text, $lengh);
                            break;
                        case 'rank':
                            $text = (string)( $item['rank'] ?? '');
                            $log['rank'] = $text;
                            $out .= $this->printColum($text, $lengh);
                            break;
                        case 'first_seen':
                        case 'lost_date':
                            $text = $item[$col] ?? '';
                            $out .= $this->printColum($text, $lengh);
                            break;
                    }
                }
                $log['created_at'] = $date;
                $log['updated_at'] = $date;
                $dbLogs[] = $log;

                $out .= PHP_EOL;
                $out .= $this->printDivider('-');
            }
        }

        DB::table(self::LOG_TABLE)->insert($dbLogs);

        echo $out;
    }

    /**
     * @param string $text
     * @param int $lengh
     * @return string
     */
    private function printColum(string $text, int $lengh){
        $out = ' '.$text;
        $i = strlen($text)+1;
        for ($i; $i<$lengh; $i++){
            $out .= ' ';
        }
        $out .= '|';

        return $out;
    }

    /**
     * @param string $type
     * @return string
     */
    private function printDivider($type = '-'){
        $out = '';
        for ($i=0; $i<self::lenghTable; $i++){
            $out .= $type;
        }
        $out .= PHP_EOL;
        return $out;
    }

}
