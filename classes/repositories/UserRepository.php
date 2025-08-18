<?php

class UserRepository {
    private $filename;
    private $headers;

    public function __construct() {
        $this->filename = dirname(__FILE__) . '/../../data/users.csv';
        $this->headers = array('利用者ID', '氏名', '連絡先', '登録日');
        $this->initializeFile();
    }

    private function initializeFile() {
        if (!file_exists($this->filename)) {
            $dir = dirname($this->filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            CsvHandler::writeCsv($this->filename, array(), $this->headers);
        }
    }

    public function findAll() {
        $data = CsvHandler::readCsv($this->filename, true);
        $users = array();

        foreach ($data as $row) {
            $users[] = User::fromArray($row);
        }

        return $users;
    }

    public function findByID($userID) {
        $users = $this->findAll();

        foreach ($users as $user) {
            if ($user->getUserID() === $userID) {
                return $user;
            }
        }

        return null;
    }

    public function findByName($name) {
        $users = $this->findAll();
        $results = array();

        foreach ($users as $user) {
            if (stripos($user->getName(), $name) !== false) {
                $results[] = $user;
            }
        }

        return $results;
    }

    public function save(User $user) {
        $existingUser = $this->findByID($user->getUserID());

        if ($existingUser !== null) {
            return $this->update($user);
        } else {
            return $this->create($user);
        }
    }

    private function create(User $user) {
        $existingUser = $this->findByID($user->getUserID());
        if ($existingUser !== null) {
            throw new Exception("利用者ID " . $user->getUserID() . " はすでに登録されています");
        }

        CsvHandler::appendCsv($this->filename, $user->toArray());
        return $user;
    }

    private function update(User $user) {
        $users = $this->findAll();
        $updated = false;

        foreach ($users as $index => $existingUser) {
            if ($existingUser->getUserID() === $user->getUserID()) {
                $users[$index] = $user;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            throw new Exception("更新対象の利用者が見つかりません");
        }

        $this->saveAll($users);
        return $user;
    }

    public function delete($userID) {
        $users = $this->findAll();
        $newUsers = array();
        $deleted = false;

        foreach ($users as $user) {
            if ($user->getUserID() !== $userID) {
                $newUsers[] = $user;
            } else {
                $deleted = true;
            }
        }

        if (!$deleted) {
            throw new Exception("削除対象の利用者が見つかりません");
        }

        $this->saveAll($newUsers);
        return true;
    }

    public function generateUserID() {
        $users = $this->findAll();
        $maxID = 0;

        foreach ($users as $user) {
            $userID = $user->getUserID();
            if (preg_match('/^U(\d+)$/', $userID, $matches)) {
                $number = intval($matches[1]);
                if ($number > $maxID) {
                    $maxID = $number;
                }
            }
        }

        return 'U' . str_pad($maxID + 1, 3, '0', STR_PAD_LEFT);
    }

    private function saveAll($users) {
        $data = array();
        foreach ($users as $user) {
            $data[] = $user->toArray();
        }

        CsvHandler::writeCsv($this->filename, $data, $this->headers);
    }
}
