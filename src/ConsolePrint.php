<?php
namespace Beatom\DataSeo;

use Illuminate\Support\Facades\DB;


class ConsolePrint {

    const configTable = [
        'excluded_target' => 30,
        'target_domain' => 30,
        'referring_domain' => 30,
        'rank' => 7,
        'first_seen' => 30,
        'lost_date' => 30,
    ];
    const lenghTable = 162;

    private $insertDB = [];

    public function getInsertDB(){
        return $this->insertDB;
    }

    /** table header
     * @return string
     */
    public function printHeader(){

        $out = '';
        $out .= '     excluded_target          |';
        $out .= '       target_domain          |';
        $out .= '       referring_domain       |';
        $out .= ' rank  |';
        $out .= '          first_seen          |';
        $out .= '          lost_date           |';
        $out .= PHP_EOL;

        return $out;
    }

    /**
     * @param array $row
     * @param $excludeTargets
     * @param string|bool $target_domain
     * @return string
     */
    public function printRows(array $row, $excludeTargets, $target_domain = false){
        $out = '';
        $date = date('Y-m-d H:i:s');

        $log = [];
        foreach (ConsolePrint::configTable as $col => $lengh) {
            switch ($col) {
                case 'excluded_target':
                    $text = '';
                    if ($excludeTargets) {
                        $text = $excludeTargets;
                    }
                    $log['excluded_target'] = $text;
                    $out .= $this->printColum($text, $lengh);
                    break;

                case 'target_domain':
                    $text = $target_domain ? $target_domain : $row['target_domain'];
                    $log['target_domain'] = $text;
                    $out .= $this->printColum($text, $lengh);
                    break;

                case 'referring_domain':
                    $text = $row['target'] ?? '';
                    $log['referring_domain'] = $text;
                    $out .= $this->printColum($text, $lengh);
                    break;

                case 'rank':
                    $rank = ($row['rank'] ?? 0);
                    $log['rank'] = $rank;
                    $out .= $this->printColum($rank, $lengh);
                    break;

                case 'first_seen':
                case 'lost_date':
                    $text = $row[$col] ?? '';
                    $out .= $this->printColum($text, $lengh);
                    break;
            }
        }
        $log['created_at'] = $date;
        $log['updated_at'] = $date;
        $this->insertDB[] = $log;

        $out .= PHP_EOL;
        $out .= $this->printDivider('-');

        return $out;
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
    public function printDivider($type = '-'){
        $out = '';
        for ($i=0; $i<self::lenghTable; $i++){
            $out .= $type;
        }
        $out .= PHP_EOL;
        return $out;
    }

}
