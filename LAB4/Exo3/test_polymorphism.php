<?php
require_once 'Book.php';
require_once 'Electronics.php';

// Create instances
$book = new Book("The Great Gatsby", "F. Scott Fitzgerald", 20.00);
$electronics = new Electronics("Smartphone", 500.00);

// Display info using polymorphism
$book->displayInfo();
$electronics->displayInfo();
?>