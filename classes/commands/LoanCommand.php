<?php

class LoanCommand {
    private $loanService;
    private $bookService;
    private $userService;

    public function __construct() {
        $this->loanService = new LoanService();
        $this->bookService = new BookService();
        $this->userService = new UserService();
    }

    public function execute($action, $options) {
        switch ($action) {
            case 'checkout':
                return $this->checkout($options);
            case 'return':
                return $this->returnBook($options);
            case 'list':
                return $this->listLoans();
            case 'overdue':
                return $this->overdue();
            case 'history':
                return $this->history($options);
            default:
                throw new Exception("無効なアクション: " . $action . " (有効: checkout, return, list, overdue, history)");
        }
    }

    private function checkout($options) {
        $userID = isset($options['user-id']) ? $options['user-id'] : null;
        $isbn = isset($options['isbn']) ? $options['isbn'] : null;

        if (!$userID || !$isbn) {
            throw new Exception("必要なパラメータが不足しています: --user-id, --isbn");
        }

        $loan = $this->loanService->checkoutBook($userID, $isbn);
        $book = $this->bookService->getBookByISBN($isbn);
        $user = $this->userService->getUserByID($userID);

        echo "貸出処理が完了しました: " . $book->getTitle() . " -> " . $user->getName() . " (貸出ID: " . $loan->getLoanID() . ")\n";
    }

    private function returnBook($options) {
        $loanID = isset($options['loan-id']) ? $options['loan-id'] : null;

        if (!$loanID) {
            throw new Exception("貸出IDを指定してください: --loan-id");
        }

        $loan = $this->loanService->getLoanByID($loanID);
        $book = $this->bookService->getBookByISBN($loan->getISBN());

        $this->loanService->returnBook($loanID);
        echo "返却処理が完了しました: " . $book->getTitle() . "\n";
    }

    private function listLoans() {
        $loans = $this->loanService->getActiveLoans();

        if (empty($loans)) {
            echo "現在貸出中の書籍がありません\n";
            return;
        }

        echo "=== 貸出中書籍一覧 ===\n";
        foreach ($loans as $loan) {
            $this->displayLoan($loan);
            echo "\n";
        }
    }

    private function overdue() {
        $loans = $this->loanService->getOverdueLoans();

        if (empty($loans)) {
            echo "延滞している書籍がありません\n";
            return;
        }

        echo "=== 延滞書籍一覧 ===\n";
        foreach ($loans as $loan) {
            $this->displayLoan($loan, true);
            echo "\n";
        }
    }

    private function history($options) {
        $userID = isset($options['user-id']) ? $options['user-id'] : null;

        if (!$userID) {
            throw new Exception("利用者IDを指定してください: --user-id");
        }

        try {
            $loans = $this->loanService->getUserLoanHistory($userID);
            $user = $this->userService->getUserByID($userID);

            echo "=== " . $user->getName() . " の貸出履歴 ===\n";
            foreach ($loans as $loan) {
                $this->displayLoan($loan);
                echo "\n";
            }
        } catch (Exception $e) {
            echo "貸出履歴: " . $e->getMessage() . "\n";
        }
    }

    private function displayLoan($loan, $showOverdueDays = false) {
        try {
            $book = $this->bookService->getBookByISBN($loan->getISBN());
            $user = $this->userService->getUserByID($loan->getUserID());

            echo "貸出ID: " . $loan->getLoanID() . "\n";
            echo "書籍: " . $book->getTitle() . " (" . $loan->getISBN() . ")\n";
            echo "利用者: " . $user->getName() . " (" . $loan->getUserID() . ")\n";
            echo "貸出日: " . $loan->getCheckoutDate() . "\n";
            echo "返却予定日: " . $loan->getDueDate() . "\n";

            if ($loan->getReturnDate()) {
                echo "返却日: " . $loan->getReturnDate() . "\n";
            }

            echo "状態: " . $loan->getStatus();

            if ($showOverdueDays && $loan->isOverdue()) {
                echo " (延滞" . $loan->getDaysOverdue() . "日)";
            }

            echo "\n";
        } catch (Exception $e) {
            echo "データ取得エラー: " . $e->getMessage() . "\n";
        }
    }
}
