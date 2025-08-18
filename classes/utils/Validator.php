<?php

class Validator {
    public static function validateISBN($isbn) {
        $isbn = str_replace(array('-', ' '), '', $isbn);

        if (!preg_match('/^\d{13}$/', $isbn)) {
            throw new Exception("ISBNは13桁の数字で入力してください");
        }

        return $isbn;
    }

    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("有効なメールアドレスを入力してください");
        }

        return $email;
    }

    public static function validateYear($year) {
        $currentYear = date('Y');
        $year = intval($year);

        if ($year < 1000 || $year > $currentYear + 10) {
            throw new Exception("有効な出版年を入力してください (1000-" . ($currentYear + 10) . ")");
        }

        return $year;
    }

    public static function validateCopies($copies) {
        $copies = intval($copies);

        if ($copies < 1) {
            throw new Exception("冊数は1以上で入力してください");
        }

        return $copies;
    }

    public static function validateRequired($value, $fieldName) {
        if (empty($value)) {
            throw new Exception($fieldName . "は必須です");
        }

        return $value;
    }

    public static function validateDate($date) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);

        if ($dateObj === false || $dateObj->format('Y-m-d') !== $date) {
            throw new Exception("有効な日付を入力してください (YYYY-MM-DD)");
        }

        return $date;
    }

    public static function validateUserID($userID) {
        if (!preg_match('/^U\d{3,}$/', $userID)) {
            throw new Exception("利用者IDの形式が正しくありません (U001のような形式)");
        }

        return $userID;
    }

    public static function validateLoanID($loanID) {
        if (!preg_match('/^L\d{3,}$/', $loanID)) {
            throw new Exception("貸出IDの形式が正しくありません (L001のような形式)");
        }

        return $loanID;
    }
}
