<?php
require_once 'Book.php';
require_once 'Discountable.php';

class Ebook extends Book implements Discountable {
    public $fileUrl;

    public function __construct($book_id, $title, $author, $price, $genre, $year, $fileUrl) {
        parent::__construct($book_id, $title, $author, $price, $genre, $year);
        $this->fileUrl = $fileUrl;
    }

    public function download() {
        return "Downloading eBook: {$this->title} from {$this->fileUrl}";
    }

    public function getDiscount() {
        // Example: 10% discount for eBooks
        return $this->price * 0.9;
    }
}
?>