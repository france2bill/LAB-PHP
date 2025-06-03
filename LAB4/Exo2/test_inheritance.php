<?php
require_once 'Product.php';
require_once 'Book.php';

// Create a Product object
$product = new Product("Laptop", 899.99);
$product->displayProduct();
echo "<br>";

// Create a Book object
$book = new Book("The Great Gatsby", 10.99, "F. Scott Fitzgerald", 1925, "Fiction");
$book->displayProduct();
?>