<?php

class Loan {
    private $loanID;
    private $userID;
    private $isbn;
    private $checkoutDate;
    private $dueDate;
    private $returnDate;
    private $status;

    const STATUS_LOANED = "貸出中";
    const STATUS_RETURNED = "返却済";

    public function __construct($loanID, $userID, $isbn, $checkoutDate = null, $dueDate = null, $returnDate = null, $status = null) {
        $this->loanID = $loanID;
        $this->userID = Validator::validateUserID($userID);
        $this->isbn = Validator::validateISBN($isbn);

        if ($checkoutDate === null) {
            $this->checkoutDate = date('Y-m-d');
        } else {
            $this->checkoutDate = Validator::validateDate($checkoutDate);
        }

        if ($dueDate === null) {
            $dueDateObj = new DateTime($this->checkoutDate);
            $dueDateObj->add(new DateInterval('P14D'));
            $this->dueDate = $dueDateObj->format('Y-m-d');
        } else {
            $this->dueDate = Validator::validateDate($dueDate);
        }

        $this->returnDate = $returnDate;
        $this->status = ($status === null) ? self::STATUS_LOANED : $status;
    }

    public function getLoanID() {
        return $this->loanID;
    }

    public function getUserID() {
        return $this->userID;
    }

    public function getISBN() {
        return $this->isbn;
    }

    public function getCheckoutDate() {
        return $this->checkoutDate;
    }

    public function getDueDate() {
        return $this->dueDate;
    }

    public function getReturnDate() {
        return $this->returnDate;
    }

    public function getStatus() {
        return $this->status;
    }

    public function returnBook() {
        if ($this->status === self::STATUS_RETURNED) {
            throw new Exception("この書籍はすでに返却済みです");
        }

        $this->returnDate = date('Y-m-d');
        $this->status = self::STATUS_RETURNED;
    }

    public function isOverdue() {
        if ($this->status === self::STATUS_RETURNED) {
            return false;
        }

        return date('Y-m-d') > $this->dueDate;
    }

    public function isLoaned() {
        return $this->status === self::STATUS_LOANED;
    }

    public function getDaysOverdue() {
        if (!$this->isOverdue()) {
            return 0;
        }

        $today = new DateTime();
        $dueDate = new DateTime($this->dueDate);
        $diff = $today->diff($dueDate);

        return $diff->days;
    }

    public function toArray() {
        return array(
            '貸出ID' => $this->loanID,
            '利用者ID' => $this->userID,
            'ISBN' => $this->isbn,
            '貸出日' => $this->checkoutDate,
            '返却予定日' => $this->dueDate,
            '返却日' => $this->returnDate,
            '状態' => $this->status
        );
    }

    public static function fromArray($data) {
        return new Loan(
            $data['貸出ID'],
            $data['利用者ID'],
            $data['ISBN'],
            $data['貸出日'],
            $data['返却予定日'],
            $data['返却日'],
            $data['状態']
        );
    }
}
