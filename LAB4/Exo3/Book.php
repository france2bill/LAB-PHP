<?php
require_once 'Discountable.php';

class Book implements Discountable {
    public string $title;
    public string $author;
    public float $price;

    public function __construct(string $title, string $author, float $price) {
        $this->title = $title;
        $this->author = $author;
        $this->price = $price;
    }

    public function getDiscount(): float {
        return $this->price * 0.10; // 10% discount for books
    }

    public function displayInfo() {
        echo "Book: {$this->title}, Author: {$this->author}, Price: {$this->price} USD, Discount: " . $this->getDiscount() . " USD <br>";
    }
}
?>