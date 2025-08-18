<?php

use Exception;
use Book;
use Validator;

class Book {
    private $isbn;
    private $title;
    private $author;
    private $publisher;
    private $year;
    private $category;
    private $totalCopies;
    private $loanedCopies;
    
    public function __construct($isbn, $title, $author, $publisher, $year, $category, $totalCopies = 1, $loanedCopies = 0) {
        $this->isbn = Validator::validateISBN($isbn);
        $this->title = Validator::validateRequired($title, "タイトル");
        $this->author = Validator::validateRequired($author, "著者");
        $this->publisher = Validator::validateRequired($publisher, "出版社");
        $this->year = Validator::validateYear($year);
        $this->category = Validator::validateRequired($category, "カテゴリ");
        $this->totalCopies = Validator::validateCopies($totalCopies);
        $this->loanedCopies = max(0, intval($loanedCopies));
    }
    
    public function getISBN() {
        return $this->isbn;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getAuthor() {
        return $this->author;
    }
    
    public function getPublisher() {
        return $this->publisher;
    }
    
    public function getYear() {
        return $this->year;
    }
    
    public function getCategory() {
        return $this->category;
    }
    
    public function getTotalCopies() {
        return $this->totalCopies;
    }
    
    public function getLoanedCopies() {
        return $this->loanedCopies;
    }
    
    public function getAvailableCopies() {
        return $this->totalCopies - $this->loanedCopies;
    }
    
    public function setTitle($title) {
        $this->title = Validator::validateRequired($title, "タイトル");
    }
    
    public function setAuthor($author) {
        $this->author = Validator::validateRequired($author, "著者");
    }
    
    public function setPublisher($publisher) {
        $this->publisher = Validator::validateRequired($publisher, "出版社");
    }
    
    public function setYear($year) {
        $this->year = Validator::validateYear($year);
    }
    
    public function setCategory($category) {
        $this->category = Validator::validateRequired($category, "カテゴリ");
    }
    
    public function setTotalCopies($copies) {
        $this->totalCopies = Validator::validateCopies($copies);
    }
    
    public function incrementLoanedCopies() {
        if ($this->loanedCopies >= $this->totalCopies) {
            throw new Exception("貸出可能な冊数がありません");
        }
        $this->loanedCopies++;
    }
    
    public function decrementLoanedCopies() {
        if ($this->loanedCopies <= 0) {
            throw new Exception("返却できる冊数がありません");
        }
        $this->loanedCopies--;
    }
    
    public function isAvailable() {
        return $this->getAvailableCopies() > 0;
    }
    
    public function toArray() {
        return array(
            'ISBN' => $this->isbn,
            'タイトル' => $this->title,
            '著者' => $this->author,
            '出版社' => $this->publisher,
            '出版年' => $this->year,
            'カテゴリ' => $this->category,
            '総数' => $this->totalCopies,
            '貸出中数' => $this->loanedCopies
        );
    }
    
    public static function fromArray($data) {
        return new Book(
            $data['ISBN'],
            $data['タイトル'],
            $data['著者'],
            $data['出版社'],
            $data['出版年'],
            $data['カテゴリ'],
            $data['総数'],
            $data['貸出中数']
        );
    }
}