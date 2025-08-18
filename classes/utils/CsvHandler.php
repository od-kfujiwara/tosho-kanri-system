<?php

use Exception;

class CsvHandler {
    public static function readCsv($filename, $hasHeader = true) {
        if (!file_exists($filename)) {
            return array();
        }
        
        $handle = self::openCsvFile($filename, 'r');
        $headers = $hasHeader ? self::readHeaders($handle) : array();
        $data = self::readCsvData($handle, $hasHeader, $headers);
        
        fclose($handle);
        return $data;
    }
    
    private static function openCsvFile($filename, $mode) {
        $handle = fopen($filename, $mode);
        
        if ($handle === false) {
            throw new Exception("CSVファイルを開けません: " . $filename);
        }
        
        return $handle;
    }
    
    private static function readHeaders($handle) {
        $line = fgetcsv($handle);
        return $line !== false ? $line : array();
    }
    
    private static function readCsvData($handle, $hasHeader, $headers) {
        $data = array();
        
        while (($line = fgetcsv($handle)) !== false) {
            if ($hasHeader && !empty($headers)) {
                $data[] = self::createRowWithHeaders($line, $headers);
            } else {
                $data[] = $line;
            }
        }
        
        return $data;
    }
    
    private static function createRowWithHeaders($line, $headers) {
        $row = array();
        $headerCount = count($headers);
        
        for ($i = 0; $i < $headerCount; $i++) {
            $row[$headers[$i]] = isset($line[$i]) ? $line[$i] : '';
        }
        
        return $row;
    }
    
    public static function writeCsv($filename, $data, $headers = null) {
        $handle = fopen($filename, 'w');
        
        if ($handle === false) {
            throw new Exception("CSVファイルを作成できません: " . $filename);
        }
        
        if ($headers !== null) {
            fputcsv($handle, $headers);
        }
        
        foreach ($data as $row) {
            if (is_array($row)) {
                fputcsv($handle, array_values($row));
            } else {
                fputcsv($handle, $row);
            }
        }
        
        fclose($handle);
    }
    
    public static function appendCsv($filename, $row) {
        $handle = fopen($filename, 'a');
        
        if ($handle === false) {
            throw new Exception("CSVファイルを開けません: " . $filename);
        }
        
        if (is_array($row)) {
            fputcsv($handle, array_values($row));
        } else {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
    }
}