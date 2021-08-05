<?php

namespace Akyos\CoreBundle\Services;

class ConvertCsvToArray {

    public function __construct()
    {
    }

    public function convert($filename, $delimiter = ',', $hasHeader = false, $dropHeader = false)
    {
        if(!file_exists($filename) || !is_readable($filename)) {
            return FALSE;
        }

        $header = NULL;
        $data = [];

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
				$row = array_map(static function($value) { return str_replace('&amp;', ' ', $value); }, $row);
                if ($hasHeader && !$dropHeader) {
                	if(!$header) {
                        $header = $row;
                    } else {
                		if(count($header) !== count($row)) {
							dd($header, $row);
						}
                        $data[] = array_combine($header, $row);
                    }
                } else {
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
        if($hasHeader && $dropHeader) {
        	array_shift($data);
		}
        return $data;
    }
}
