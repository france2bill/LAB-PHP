<?php
require_once 'Discountable.php';

class Electronics implements Discountable {
    public string $name;
    public float $price;

    public function __construct(string $name, float $price) {
        $this->name = $name;
        $this->price = $price;
    }

    public function getDiscount(): float {
        return $this->price * 0.05; // 5% discount for electronics
    }

    public function displayInfo() {
        echo "Electronics: {$this->name}, Price: {$this->price} USD, Discount: " . $this->getDiscount() . " USD <br>";
    }
}
?>