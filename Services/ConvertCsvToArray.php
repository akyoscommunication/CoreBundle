<?php

namespace Akyos\CoreBundle\Services;

class ConvertCsvToArray
{
    /**
     * @param $filename
     * @param string $delimiter
     * @param false $hasHeader
     * @param false $dropHeader
     * @return array|false
     */
    public function convert($filename, $delimiter = ',', $hasHeader = false, $dropHeader = false)
    {
        if(!file_exists($filename) || !is_readable($filename)) {
            return FALSE;
        }

        $header = NULL;
        $data = [];

        if (($handle = fopen($filename, 'rb')) !== FALSE) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                if ($hasHeader && !$dropHeader) {
                	if(!$header) {
                        $header = $row;
                    } else {
                        $data[] = array_combine($header, $row);
                    }
                } else {
					$data[] = str_getcsv($row);
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
