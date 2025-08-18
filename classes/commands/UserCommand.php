<?php

use Exception;
use UserService;

class UserCommand {
    private $userService;
    
    public function __construct() {
        $this->userService = new UserService();
    }
    
    public function execute($action, $options) {
        switch ($action) {
            case 'add':
                return $this->add($options);
            case 'list':
                return $this->listUsers();
            case 'search':
                return $this->search($options);
            case 'show':
                return $this->show($options);
            case 'edit':
                return $this->edit($options);
            case 'delete':
                return $this->delete($options);
            default:
                throw new Exception("無効なアクション: " . $action . " (有効: add, list, search, show, edit, delete)");
        }
    }
    
    private function add($options) {
        $name = isset($options['name']) ? $options['name'] : null;
        $email = isset($options['email']) ? $options['email'] : null;
        
        if (!$name || !$email) {
            throw new Exception("必要なパラメータが不足しています: --name, --email");
        }
        
        $user = $this->userService->addUser($name, $email);
        echo "利用者を登録しました: " . $user->getName() . " (ID: " . $user->getUserID() . ")\n";
    }
    
    private function listUsers() {
        $users = $this->userService->getAllUsers();
        
        if (empty($users)) {
            echo "登録されている利用者がありません\n";
            return;
        }
        
        echo "=== 利用者一覧 ===\n";
        foreach ($users as $user) {
            $this->displayUser($user);
            echo "\n";
        }
    }
    
    private function search($options) {
        $name = isset($options['name']) ? $options['name'] : null;
        
        if (!$name) {
            throw new Exception("検索条件を指定してください: --name");
        }
        
        try {
            $users = $this->userService->searchUsersByName($name);
            
            echo "=== 検索結果 ===\n";
            foreach ($users as $user) {
                $this->displayUser($user);
                echo "\n";
            }
        } catch (Exception $e) {
            echo "検索結果: " . $e->getMessage() . "\n";
        }
    }
    
    private function show($options) {
        $id = isset($options['id']) ? $options['id'] : null;
        
        if (!$id) {
            throw new Exception("利用者IDを指定してください: --id");
        }
        
        $user = $this->userService->getUserByID($id);
        echo "=== 利用者詳細 ===\n";
        $this->displayUser($user);
    }
    
    private function edit($options) {
        $id = isset($options['id']) ? $options['id'] : null;
        
        if (!$id) {
            throw new Exception("利用者IDを指定してください: --id");
        }
        
        $updateOptions = array();
        if (isset($options['name'])) $updateOptions['name'] = $options['name'];
        if (isset($options['email'])) $updateOptions['email'] = $options['email'];
        
        if (empty($updateOptions)) {
            throw new Exception("更新する項目を指定してください: --name, --email");
        }
        
        $user = $this->userService->updateUser($id, $updateOptions);
        echo "利用者を更新しました: " . $user->getName() . " (ID: " . $user->getUserID() . ")\n";
    }
    
    private function delete($options) {
        $id = isset($options['id']) ? $options['id'] : null;
        
        if (!$id) {
            throw new Exception("利用者IDを指定してください: --id");
        }
        
        $user = $this->userService->getUserByID($id);
        $name = $user->getName();
        
        $this->userService->deleteUser($id);
        echo "利用者を削除しました: " . $name . " (ID: " . $id . ")\n";
    }
    
    private function displayUser($user) {
        echo "利用者ID: " . $user->getUserID() . "\n";
        echo "氏名: " . $user->getName() . "\n";
        echo "メールアドレス: " . $user->getEmail() . "\n";
        echo "登録日: " . $user->getRegistrationDate() . "\n";
    }
}