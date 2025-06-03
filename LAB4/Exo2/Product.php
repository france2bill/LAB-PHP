<?php
class Product {
    public string $name;
    public float $price;

    // Constructor to initialize properties
    public function __construct(string $name, float $price) {
        $this->name = $name;
        $this->price = $price;
    }

    // Method to display product details
    public function displayProduct() {
        echo "Product Name: {$this->name} <br>";
        echo "Price: {$this->price} USD <br>";
    }
}
?>