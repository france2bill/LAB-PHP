<?php
require_once 'Product.php';

class Book extends Product {
    public string $author;
    public int $publication_year;
    public string $genre;

    // Constructor with parent class properties + book-specific properties
    public function __construct(string $name, float $price, string $author, int $publication_year, string $genre) {
        parent::__construct($name, $price); // Call the parent constructor
        $this->author = $author;
        $this->publication_year = $publication_year;
        $this->genre = $genre;
    }

    // Override the displayProduct method
    public function displayProduct() {
        echo "Book Title: {$this->name} <br>";
        echo "Author: {$this->author} <br>";
        echo "Publication Year: {$this->publication_year} <br>";
        echo "Genre: {$this->genre} <br>";
        echo "Price: {$this->price} USD <br>";
    }
}
?>