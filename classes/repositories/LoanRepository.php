<?php

use Exception;

class LoanRepository {
    private $filename;
    private $headers;
    
    public function __construct() {
        $this->filename = dirname(__FILE__) . '/../../data/loans.csv';
        $this->headers = array('貸出ID', '利用者ID', 'ISBN', '貸出日', '返却予定日', '返却日', '状態');
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
        $loans = array();
        
        foreach ($data as $row) {
            $loans[] = Loan::fromArray($row);
        }
        
        return $loans;
    }
    
    public function findByID($loanID) {
        $loans = $this->findAll();
        
        foreach ($loans as $loan) {
            if ($loan->getLoanID() === $loanID) {
                return $loan;
            }
        }
        
        return null;
    }
    
    public function findByUserID($userID) {
        $loans = $this->findAll();
        $results = array();
        
        foreach ($loans as $loan) {
            if ($loan->getUserID() === $userID) {
                $results[] = $loan;
            }
        }
        
        return $results;
    }
    
    public function findByISBN($isbn) {
        $loans = $this->findAll();
        $results = array();
        
        foreach ($loans as $loan) {
            if ($loan->getISBN() === $isbn) {
                $results[] = $loan;
            }
        }
        
        return $results;
    }
    
    public function findActiveLoans() {
        $loans = $this->findAll();
        $results = array();
        
        foreach ($loans as $loan) {
            if ($loan->isLoaned()) {
                $results[] = $loan;
            }
        }
        
        return $results;
    }
    
    public function findOverdueLoans() {
        $loans = $this->findActiveLoans();
        $results = array();
        
        foreach ($loans as $loan) {
            if ($loan->isOverdue()) {
                $results[] = $loan;
            }
        }
        
        return $results;
    }
    
    public function save(Loan $loan) {
        $existingLoan = $this->findByID($loan->getLoanID());
        
        if ($existingLoan !== null) {
            return $this->update($loan);
        } else {
            return $this->create($loan);
        }
    }
    
    private function create(Loan $loan) {
        $existingLoan = $this->findByID($loan->getLoanID());
        if ($existingLoan !== null) {
            throw new Exception("貸出ID " . $loan->getLoanID() . " はすでに登録されています");
        }
        
        CsvHandler::appendCsv($this->filename, $loan->toArray());
        return $loan;
    }
    
    private function update(Loan $loan) {
        $loans = $this->findAll();
        $updated = false;
        
        foreach ($loans as $index => $existingLoan) {
            if ($existingLoan->getLoanID() === $loan->getLoanID()) {
                $loans[$index] = $loan;
                $updated = true;
                break;
            }
        }
        
        if (!$updated) {
            throw new Exception("更新対象の貸出レコードが見つかりません");
        }
        
        $this->saveAll($loans);
        return $loan;
    }
    
    public function delete($loanID) {
        $loans = $this->findAll();
        $newLoans = array();
        $deleted = false;
        
        foreach ($loans as $loan) {
            if ($loan->getLoanID() !== $loanID) {
                $newLoans[] = $loan;
            } else {
                $deleted = true;
            }
        }
        
        if (!$deleted) {
            throw new Exception("削除対象の貸出レコードが見つかりません");
        }
        
        $this->saveAll($newLoans);
        return true;
    }
    
    public function generateLoanID() {
        $loans = $this->findAll();
        $maxID = 0;
        
        foreach ($loans as $loan) {
            $loanID = $loan->getLoanID();
            if (preg_match('/^L(\d+)$/', $loanID, $matches)) {
                $number = intval($matches[1]);
                if ($number > $maxID) {
                    $maxID = $number;
                }
            }
        }
        
        return 'L' . str_pad($maxID + 1, 3, '0', STR_PAD_LEFT);
    }
    
    private function saveAll($loans) {
        $data = array();
        foreach ($loans as $loan) {
            $data[] = $loan->toArray();
        }
        
        CsvHandler::writeCsv($this->filename, $data, $this->headers);
    }
}