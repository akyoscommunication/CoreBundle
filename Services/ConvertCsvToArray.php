<?php

namespace Akyos\CoreBundle\Services;

class ConvertCsvToArray {

    public function __construct()
    {
    }

    public function convert($filename, $delimiter = ',', $hasHeader = false)
    {
        if(!file_exists($filename) || !is_readable($filename)) {
            return FALSE;
        }

        $header = NULL;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                if ($hasHeader) {
                    if(!$header) {
                        $header = $row;
                    } else {
                        $data[] = array_combine($header, $row);
                    }
                }else{
                    foreach ($row as $key => $r){
                        if($r == 'NULL'){
                            $row[$key] = null;
                        }
                    }
                    $data[] = $row;
                }
            }
            fclose($handle);
        }
        return $data;
    }

}
