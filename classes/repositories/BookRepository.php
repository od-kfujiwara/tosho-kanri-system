<?php

use Exception;

class BookRepository {
    private $filename;
    private $headers;
    
    public function __construct() {
        $this->filename = dirname(__FILE__) . '/../../data/books.csv';
        $this->headers = array('ISBN', 'タイトル', '著者', '出版社', '出版年', 'カテゴリ', '総数', '貸出中数');
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
        $books = array();
        
        foreach ($data as $row) {
            $books[] = Book::fromArray($row);
        }
        
        return $books;
    }
    
    public function findByISBN($isbn) {
        $books = $this->findAll();
        
        foreach ($books as $book) {
            if ($book->getISBN() === $isbn) {
                return $book;
            }
        }
        
        return null;
    }
    
    public function findByTitle($title) {
        $books = $this->findAll();
        $results = array();
        
        foreach ($books as $book) {
            if (stripos($book->getTitle(), $title) !== false) {
                $results[] = $book;
            }
        }
        
        return $results;
    }
    
    public function findByAuthor($author) {
        $books = $this->findAll();
        $results = array();
        
        foreach ($books as $book) {
            if (stripos($book->getAuthor(), $author) !== false) {
                $results[] = $book;
            }
        }
        
        return $results;
    }
    
    public function findByCategory($category) {
        $books = $this->findAll();
        $results = array();
        
        foreach ($books as $book) {
            if (stripos($book->getCategory(), $category) !== false) {
                $results[] = $book;
            }
        }
        
        return $results;
    }
    
    public function save(Book $book) {
        $existingBook = $this->findByISBN($book->getISBN());
        
        if ($existingBook !== null) {
            return $this->update($book);
        } else {
            return $this->create($book);
        }
    }
    
    private function create(Book $book) {
        $existingBook = $this->findByISBN($book->getISBN());
        if ($existingBook !== null) {
            throw new Exception("ISBN " . $book->getISBN() . " はすでに登録されています");
        }
        
        CsvHandler::appendCsv($this->filename, $book->toArray());
        return $book;
    }
    
    private function update(Book $book) {
        $books = $this->findAll();
        $updated = false;
        
        foreach ($books as $index => $existingBook) {
            if ($existingBook->getISBN() === $book->getISBN()) {
                $books[$index] = $book;
                $updated = true;
                break;
            }
        }
        
        if (!$updated) {
            throw new Exception("更新対象の書籍が見つかりません");
        }
        
        $this->saveAll($books);
        return $book;
    }
    
    public function delete($isbn) {
        $books = $this->findAll();
        $newBooks = array();
        $deleted = false;
        
        foreach ($books as $book) {
            if ($book->getISBN() !== $isbn) {
                $newBooks[] = $book;
            } else {
                $deleted = true;
            }
        }
        
        if (!$deleted) {
            throw new Exception("削除対象の書籍が見つかりません");
        }
        
        $this->saveAll($newBooks);
        return true;
    }
    
    private function saveAll($books) {
        $data = array();
        foreach ($books as $book) {
            $data[] = $book->toArray();
        }
        
        CsvHandler::writeCsv($this->filename, $data, $this->headers);
    }
}