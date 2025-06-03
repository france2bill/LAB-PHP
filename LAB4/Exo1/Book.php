<?php
class Book {
    public string $title;
    public string $author;
    public int $publication_year;
    public string $genre;
    public float $price;

    // Constructor to initialize properties
    public function __construct(string $title, string $author, int $publication_year, string $genre, float $price) {
        $this->title = $title;
        $this->author = $author;
        $this->publication_year = $publication_year;
        $this->genre = $genre;
        $this->price = $price;
    }

    // Method to display book details
    public function displayBookInfo() {
        echo "Title: {$this->title} <br>";
        echo "Author: {$this->author} <br>";
        echo "Publication Year: {$this->publication_year} <br>";
        echo "Genre: {$this->genre} <br>";
        echo "Price: {$this->price} USD <br>";
    }
}
?>