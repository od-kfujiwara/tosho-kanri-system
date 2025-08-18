<?php

use Exception;
use CommandParser;
use BookCommand;
use UserCommand;
use LoanCommand;

// Include all necessary classes
require_once 'classes/utils/CsvHandler.php';
require_once 'classes/utils/CommandParser.php';
require_once 'classes/utils/Validator.php';

require_once 'classes/models/Book.php';
require_once 'classes/models/User.php';
require_once 'classes/models/Loan.php';

require_once 'classes/repositories/BookRepository.php';
require_once 'classes/repositories/UserRepository.php';
require_once 'classes/repositories/LoanRepository.php';

require_once 'classes/services/BookService.php';
require_once 'classes/services/UserService.php';
require_once 'classes/services/LoanService.php';

require_once 'classes/commands/BookCommand.php';
require_once 'classes/commands/UserCommand.php';
require_once 'classes/commands/LoanCommand.php';

function showUsage() {
    echo "図書管理システム\n";
    echo "\n";
    echo "使用方法:\n";
    echo "  php library.php [entity] [action] [options]\n";
    echo "\n";
    echo "エンティティ:\n";
    echo "  book  - 書籍管理\n";
    echo "  user  - 利用者管理\n";
    echo "  loan  - 貸出・返却管理\n";
    echo "\n";
    echo "書籍管理:\n";
    echo "  php library.php book add --isbn=<ISBN> --title=<タイトル> --author=<著者> --publisher=<出版社> --year=<出版年> --category=<カテゴリ> [--copies=<冊数>]\n";
    echo "  php library.php book list\n";
    echo "  php library.php book search [--title=<タイトル>|--author=<著者>|--category=<カテゴリ>]\n";
    echo "  php library.php book show --isbn=<ISBN>\n";
    echo "  php library.php book edit --isbn=<ISBN> [--title=<タイトル>] [--author=<著者>] [--publisher=<出版社>] [--year=<出版年>] [--category=<カテゴリ>] [--copies=<冊数>]\n";
    echo "  php library.php book delete --isbn=<ISBN>\n";
    echo "\n";
    echo "利用者管理:\n";
    echo "  php library.php user add --name=<氏名> --email=<メールアドレス>\n";
    echo "  php library.php user list\n";
    echo "  php library.php user search --name=<氏名>\n";
    echo "  php library.php user show --id=<利用者ID>\n";
    echo "  php library.php user edit --id=<利用者ID> [--name=<氏名>] [--email=<メールアドレス>]\n";
    echo "  php library.php user delete --id=<利用者ID>\n";
    echo "\n";
    echo "貸出・返却管理:\n";
    echo "  php library.php loan checkout --user-id=<利用者ID> --isbn=<ISBN>\n";
    echo "  php library.php loan return --loan-id=<貸出ID>\n";
    echo "  php library.php loan list\n";
    echo "  php library.php loan overdue\n";
    echo "  php library.php loan history --user-id=<利用者ID>\n";
    echo "\n";
}

function main($argv) {
    try {
        if (count($argv) < 2) {
            showUsage();
            return;
        }
        
        $parser = new CommandParser($argv);
        $parser->validate();
        
        $entity = $parser->getEntity();
        $action = $parser->getAction();
        $options = $parser->getAllOptions();
        
        switch ($entity) {
            case 'book':
                $command = new BookCommand();
                $command->execute($action, $options);
                break;
                
            case 'user':
                $command = new UserCommand();
                $command->execute($action, $options);
                break;
                
            case 'loan':
                $command = new LoanCommand();
                $command->execute($action, $options);
                break;
                
            default:
                throw new Exception("無効なエンティティ: " . $entity);
        }
        
    } catch (Exception $e) {
        echo "エラー: " . $e->getMessage() . "\n\n";
        
        // Show usage for common errors
        if (strpos($e->getMessage(), "エンティティを指定") !== false ||
            strpos($e->getMessage(), "アクションを指定") !== false ||
            strpos($e->getMessage(), "無効なエンティティ") !== false) {
            showUsage();
        }
    }
}

// Check if script is being run directly
if (php_sapi_name() === 'cli') {
    main($argv);
}