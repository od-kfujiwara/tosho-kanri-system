<?php

use Exception;

class CommandParser {
    private $entity;
    private $action;
    private $options;
    
    public function __construct($argv) {
        $this->entity = isset($argv[1]) ? $argv[1] : null;
        $this->action = isset($argv[2]) ? $argv[2] : null;
        $this->options = array();
        
        $argvCount = count($argv);
        for ($i = 3; $i < $argvCount; $i++) {
            $arg = $argv[$i];
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', $arg, 2);
                $key = substr($parts[0], 2);
                $value = isset($parts[1]) ? $parts[1] : true;
                $this->options[$key] = $value;
            }
        }
    }
    
    public function getEntity() {
        return $this->entity;
    }
    
    public function getAction() {
        return $this->action;
    }
    
    public function getOption($key, $default = null) {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }
    
    public function getAllOptions() {
        return $this->options;
    }
    
    public function hasOption($key) {
        return isset($this->options[$key]);
    }
    
    public function validate() {
        if (empty($this->entity)) {
            throw new Exception("エンティティを指定してください (book, user, loan)");
        }
        
        if (empty($this->action)) {
            throw new Exception("アクションを指定してください");
        }
        
        $validEntities = array('book', 'user', 'loan');
        if (!in_array($this->entity, $validEntities)) {
            throw new Exception("無効なエンティティ: " . $this->entity . " (有効: " . implode(', ', $validEntities) . ")");
        }
        
        return true;
    }
}