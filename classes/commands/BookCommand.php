<?php

use Exception;
use BookService;

class BookCommand {
    private $bookService;
    
    public function __construct() {
        $this->bookService = new BookService();
    }
    
    public function execute($action, $options) {
        switch ($action) {
            case 'add':
                return $this->add($options);
            case 'list':
                return $this->listBooks();
            case 'search':
                return $this->search($options);
            case 'show':
                return $this->show($options);
            case 'edit':
                return $this->edit($options);
            case 'delete':
                return $this->delete($options);
            default:
                throw new Exception("無効なアクション: " . $action . " (有効: add, list, search, show, edit, delete)");
        }
    }
    
    private function add($options) {
        $bookData = $this->extractBookDataFromOptions($options);
        $this->validateRequiredBookFields($bookData);
        
        $book = $this->bookService->addBook(
            $bookData['isbn'],
            $bookData['title'],
            $bookData['author'],
            $bookData['publisher'],
            $bookData['year'],
            $bookData['category'],
            $bookData['copies']
        );
        
        echo "書籍を登録しました: " . $book->getTitle() . "\n";
    }
    
    private function extractBookDataFromOptions($options) {
        return array(
            'isbn' => isset($options['isbn']) ? $options['isbn'] : null,
            'title' => isset($options['title']) ? $options['title'] : null,
            'author' => isset($options['author']) ? $options['author'] : null,
            'publisher' => isset($options['publisher']) ? $options['publisher'] : null,
            'year' => isset($options['year']) ? $options['year'] : null,
            'category' => isset($options['category']) ? $options['category'] : null,
            'copies' => isset($options['copies']) ? $options['copies'] : 1
        );
    }
    
    private function validateRequiredBookFields($bookData) {
        $requiredFields = array('isbn', 'title', 'author', 'publisher', 'year', 'category');
        
        foreach ($requiredFields as $field) {
            if (!$bookData[$field]) {
                throw new Exception("必要なパラメータが不足しています: --isbn, --title, --author, --publisher, --year, --category");
            }
        }
    }
    
    private function listBooks() {
        $books = $this->bookService->getAllBooks();
        
        if (empty($books)) {
            echo "登録されている書籍がありません\n";
            return;
        }
        
        echo "=== 書籍一覧 ===\n";
        foreach ($books as $book) {
            $this->displayBook($book);
            echo "\n";
        }
    }
    
    private function search($options) {
        $searchCriteria = $this->extractSearchCriteria($options);
        $this->validateSearchCriteria($searchCriteria);
        
        try {
            $books = $this->performSearch($searchCriteria);
            $this->displaySearchResults($books);
        } catch (Exception $e) {
            echo "検索結果: " . $e->getMessage() . "\n";
        }
    }
    
    private function extractSearchCriteria($options) {
        return array(
            'title' => isset($options['title']) ? $options['title'] : null,
            'author' => isset($options['author']) ? $options['author'] : null,
            'category' => isset($options['category']) ? $options['category'] : null
        );
    }
    
    private function validateSearchCriteria($searchCriteria) {
        if (!$searchCriteria['title'] && !$searchCriteria['author'] && !$searchCriteria['category']) {
            throw new Exception("検索条件を指定してください: --title, --author, --category のいずれか");
        }
    }
    
    private function performSearch($searchCriteria) {
        if ($searchCriteria['title']) {
            return $this->bookService->searchBooksByTitle($searchCriteria['title']);
        } elseif ($searchCriteria['author']) {
            return $this->bookService->searchBooksByAuthor($searchCriteria['author']);
        } elseif ($searchCriteria['category']) {
            return $this->bookService->searchBooksByCategory($searchCriteria['category']);
        }
        
        return array();
    }
    
    private function displaySearchResults($books) {
        echo "=== 検索結果 ===\n";
        foreach ($books as $book) {
            $this->displayBook($book);
            echo "\n";
        }
    }
    
    private function show($options) {
        $isbn = isset($options['isbn']) ? $options['isbn'] : null;
        
        if (!$isbn) {
            throw new Exception("ISBNを指定してください: --isbn");
        }
        
        $book = $this->bookService->getBookByISBN($isbn);
        echo "=== 書籍詳細 ===\n";
        $this->displayBook($book);
    }
    
    private function edit($options) {
        $isbn = $this->validateIsbnOption($options);
        $updateOptions = $this->extractUpdateOptions($options);
        $this->validateUpdateOptions($updateOptions);
        
        $book = $this->bookService->updateBook($isbn, $updateOptions);
        echo "書籍を更新しました: " . $book->getTitle() . "\n";
    }
    
    private function validateIsbnOption($options) {
        $isbn = isset($options['isbn']) ? $options['isbn'] : null;
        
        if (!$isbn) {
            throw new Exception("ISBNを指定してください: --isbn");
        }
        
        return $isbn;
    }
    
    private function extractUpdateOptions($options) {
        $updateFields = array('title', 'author', 'publisher', 'year', 'category', 'copies');
        $updateOptions = array();
        
        foreach ($updateFields as $field) {
            if (isset($options[$field])) {
                $updateOptions[$field] = $options[$field];
            }
        }
        
        return $updateOptions;
    }
    
    private function validateUpdateOptions($updateOptions) {
        if (empty($updateOptions)) {
            throw new Exception("更新する項目を指定してください: --title, --author, --publisher, --year, --category, --copies");
        }
    }
    
    private function delete($options) {
        $isbn = isset($options['isbn']) ? $options['isbn'] : null;
        
        if (!$isbn) {
            throw new Exception("ISBNを指定してください: --isbn");
        }
        
        $book = $this->bookService->getBookByISBN($isbn);
        $title = $book->getTitle();
        
        $this->bookService->deleteBook($isbn);
        echo "書籍を削除しました: " . $title . "\n";
    }
    
    private function displayBook($book) {
        echo "ISBN: " . $book->getISBN() . "\n";
        echo "タイトル: " . $book->getTitle() . "\n";
        echo "著者: " . $book->getAuthor() . "\n";
        echo "出版社: " . $book->getPublisher() . "\n";
        echo "出版年: " . $book->getYear() . "\n";
        echo "カテゴリ: " . $book->getCategory() . "\n";
        echo "状況: ";
        
        if ($book->isAvailable()) {
            echo "利用可能 (" . $book->getTotalCopies() . "冊中" . $book->getAvailableCopies() . "冊利用可能)\n";
        } else {
            echo "貸出中 (" . $book->getTotalCopies() . "冊すべて貸出中)\n";
        }
    }
}