<?php
require_once 'Book.php';

// Creating an object of the Book class
$book1 = new Book("The Great Gatsby", "F. Scott Fitzgerald", 1925, "Fiction", 10.99);

// Displaying book details
$book1->displayBookInfo();
?>