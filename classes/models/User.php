<?php

class User {
    private $userID;
    private $name;
    private $email;
    private $registrationDate;

    public function __construct($userID, $name, $email, $registrationDate = null) {
        $this->userID = $userID;
        $this->name = Validator::validateRequired($name, "氏名");
        $this->email = Validator::validateEmail($email);

        if ($registrationDate === null) {
            $this->registrationDate = date('Y-m-d');
        } else {
            $this->registrationDate = Validator::validateDate($registrationDate);
        }
    }

    public function getUserID() {
        return $this->userID;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRegistrationDate() {
        return $this->registrationDate;
    }

    public function setName($name) {
        $this->name = Validator::validateRequired($name, "氏名");
    }

    public function setEmail($email) {
        $this->email = Validator::validateEmail($email);
    }

    public function toArray() {
        return array(
            '利用者ID' => $this->userID,
            '氏名' => $this->name,
            '連絡先' => $this->email,
            '登録日' => $this->registrationDate
        );
    }

    public static function fromArray($data) {
        return new User(
            $data['利用者ID'],
            $data['氏名'],
            $data['連絡先'],
            $data['登録日']
        );
    }
}
