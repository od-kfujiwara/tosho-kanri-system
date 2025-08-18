<?php

use Exception;
use LoanRepository;
use BookService;
use UserService;
use Loan;

class LoanService {
    private $loanRepository;
    private $bookService;
    private $userService;
    
    public function __construct() {
        $this->loanRepository = new LoanRepository();
        $this->bookService = new BookService();
        $this->userService = new UserService();
    }
    
    public function checkoutBook($userID, $isbn) {
        // Validate user exists
        $this->userService->getUserByID($userID);
        
        // Validate book exists and is available
        $book = $this->bookService->getBookByISBN($isbn);
        if (!$book->isAvailable()) {
            throw new Exception("この書籍は現在貸出中で利用できません");
        }
        
        // Check if user already has this book loaned
        $userLoans = $this->loanRepository->findByUserID($userID);
        foreach ($userLoans as $loan) {
            if ($loan->getISBN() === $isbn && $loan->isLoaned()) {
                throw new Exception("この利用者は既にこの書籍を借りています");
            }
        }
        
        // Generate loan ID and create loan
        $loanID = $this->loanRepository->generateLoanID();
        $loan = new Loan($loanID, $userID, $isbn);
        
        // Update book loan count
        $this->bookService->loanBook($isbn);
        
        // Save loan record
        return $this->loanRepository->save($loan);
    }
    
    public function returnBook($loanID) {
        $loan = $this->getLoanByID($loanID);
        
        if (!$loan->isLoaned()) {
            throw new Exception("この貸出は既に返却済みです");
        }
        
        // Update loan status
        $loan->returnBook();
        
        // Update book loan count
        $this->bookService->returnBook($loan->getISBN());
        
        // Save updated loan record
        return $this->loanRepository->save($loan);
    }
    
    public function getAllLoans() {
        return $this->loanRepository->findAll();
    }
    
    public function getActiveLoans() {
        return $this->loanRepository->findActiveLoans();
    }
    
    public function getOverdueLoans() {
        return $this->loanRepository->findOverdueLoans();
    }
    
    public function getLoanByID($loanID) {
        $loan = $this->loanRepository->findByID($loanID);
        if ($loan === null) {
            throw new Exception("貸出ID " . $loanID . " が見つかりません");
        }
        return $loan;
    }
    
    public function getUserLoanHistory($userID) {
        // Validate user exists
        $this->userService->getUserByID($userID);
        
        $loans = $this->loanRepository->findByUserID($userID);
        if (empty($loans)) {
            throw new Exception("この利用者の貸出履歴がありません");
        }
        
        return $loans;
    }
    
    public function getBookLoanHistory($isbn) {
        // Validate book exists
        $this->bookService->getBookByISBN($isbn);
        
        return $this->loanRepository->findByISBN($isbn);
    }
    
    public function getLoanSummary() {
        $allLoans = $this->getAllLoans();
        $activeLoans = $this->getActiveLoans();
        $overdueLoans = $this->getOverdueLoans();
        
        return array(
            'total_loans' => count($allLoans),
            'active_loans' => count($activeLoans),
            'overdue_loans' => count($overdueLoans),
            'returned_loans' => count($allLoans) - count($activeLoans)
        );
    }
}