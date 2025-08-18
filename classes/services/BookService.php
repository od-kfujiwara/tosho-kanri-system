<?php

use Exception;
use BookRepository;
use Book;

class BookService {
    private $bookRepository;
    
    public function __construct() {
        $this->bookRepository = new BookRepository();
    }
    
    public function addBook($isbn, $title, $author, $publisher, $year, $category, $copies = 1) {
        $book = new Book($isbn, $title, $author, $publisher, $year, $category, $copies);
        return $this->bookRepository->save($book);
    }
    
    public function getAllBooks() {
        return $this->bookRepository->findAll();
    }
    
    public function getBookByISBN($isbn) {
        $book = $this->bookRepository->findByISBN($isbn);
        if ($book === null) {
            throw new Exception("ISBN " . $isbn . " の書籍が見つかりません");
        }
        return $book;
    }
    
    public function searchBooksByTitle($title) {
        $books = $this->bookRepository->findByTitle($title);
        if (empty($books)) {
            throw new Exception("タイトルに \"" . $title . "\" を含む書籍が見つかりません");
        }
        return $books;
    }
    
    public function searchBooksByAuthor($author) {
        $books = $this->bookRepository->findByAuthor($author);
        if (empty($books)) {
            throw new Exception("著者に \"" . $author . "\" を含む書籍が見つかりません");
        }
        return $books;
    }
    
    public function searchBooksByCategory($category) {
        $books = $this->bookRepository->findByCategory($category);
        if (empty($books)) {
            throw new Exception("カテゴリに \"" . $category . "\" を含む書籍が見つかりません");
        }
        return $books;
    }
    
    public function updateBook($isbn, $options = array()) {
        $book = $this->getBookByISBN($isbn);
        
        if (isset($options['title'])) {
            $book->setTitle($options['title']);
        }
        if (isset($options['author'])) {
            $book->setAuthor($options['author']);
        }
        if (isset($options['publisher'])) {
            $book->setPublisher($options['publisher']);
        }
        if (isset($options['year'])) {
            $book->setYear($options['year']);
        }
        if (isset($options['category'])) {
            $book->setCategory($options['category']);
        }
        if (isset($options['copies'])) {
            $book->setTotalCopies($options['copies']);
        }
        
        return $this->bookRepository->save($book);
    }
    
    public function deleteBook($isbn) {
        $book = $this->getBookByISBN($isbn);
        
        if ($book->getLoanedCopies() > 0) {
            throw new Exception("貸出中の書籍は削除できません");
        }
        
        return $this->bookRepository->delete($isbn);
    }
    
    public function loanBook($isbn) {
        $book = $this->getBookByISBN($isbn);
        
        if (!$book->isAvailable()) {
            throw new Exception("この書籍は貸出中です");
        }
        
        $book->incrementLoanedCopies();
        return $this->bookRepository->save($book);
    }
    
    public function returnBook($isbn) {
        $book = $this->getBookByISBN($isbn);
        
        if ($book->getLoanedCopies() <= 0) {
            throw new Exception("この書籍に返却すべき貸出はありません");
        }
        
        $book->decrementLoanedCopies();
        return $this->bookRepository->save($book);
    }
    
    public function getAvailableBooks() {
        $books = $this->getAllBooks();
        $availableBooks = array();
        
        foreach ($books as $book) {
            if ($book->isAvailable()) {
                $availableBooks[] = $book;
            }
        }
        
        return $availableBooks;
    }
    
    public function getLoanedBooks() {
        $books = $this->getAllBooks();
        $loanedBooks = array();
        
        foreach ($books as $book) {
            if ($book->getLoanedCopies() > 0) {
                $loanedBooks[] = $book;
            }
        }
        
        return $loanedBooks;
    }
}