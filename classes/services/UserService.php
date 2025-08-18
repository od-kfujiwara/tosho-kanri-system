<?php

class UserService {
    private $userRepository;

    public function __construct() {
        $this->userRepository = new UserRepository();
    }

    public function addUser($name, $email) {
        $userID = $this->userRepository->generateUserID();
        $user = new User($userID, $name, $email);
        return $this->userRepository->save($user);
    }

    public function getAllUsers() {
        return $this->userRepository->findAll();
    }

    public function getUserByID($userID) {
        $user = $this->userRepository->findByID($userID);
        if ($user === null) {
            throw new Exception("利用者ID " . $userID . " が見つかりません");
        }
        return $user;
    }

    public function searchUsersByName($name) {
        $users = $this->userRepository->findByName($name);
        if (empty($users)) {
            throw new Exception("氏名に \"" . $name . "\" を含む利用者が見つかりません");
        }
        return $users;
    }

    public function updateUser($userID, $options = array()) {
        $user = $this->getUserByID($userID);

        if (isset($options['name'])) {
            $user->setName($options['name']);
        }
        if (isset($options['email'])) {
            $user->setEmail($options['email']);
        }

        return $this->userRepository->save($user);
    }

    public function deleteUser($userID) {
        // Check if user has active loans
        $loanRepository = new LoanRepository();
        $userLoans = $loanRepository->findByUserID($userID);

        foreach ($userLoans as $loan) {
            if ($loan->isLoaned()) {
                throw new Exception("貸出中の書籍がある利用者は削除できません");
            }
        }

        return $this->userRepository->delete($userID);
    }
}
