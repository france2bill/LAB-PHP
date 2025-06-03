<?php
class Member {
    public $member_id, $name, $email, $membership_date;

    public function __construct($member_id, $name, $email, $membership_date) {
        $this->member_id = $member_id;
        $this->name = $name;
        $this->email = $email;
        $this->membership_date = $membership_date;
    }

    public function getBorrowedBooks() {
        require 'db.php';
        $stmt = $pdo->prepare("SELECT b.* FROM BookLoans bl JOIN Books b ON bl.book_id = b.book_id WHERE bl.member_id = ? AND bl.return_date IS NULL");
        $stmt->execute([$this->member_id]);
        return $stmt->fetchAll();
    }
}
?>