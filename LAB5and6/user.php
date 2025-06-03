<?php
require_once 'db.php';



class user {
    public $id;
    public $username;
    public $email;
    public $created_at;

    public function __construct($id, $username, $email, $created_at) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->created_at = $created_at;
    }

    public function getBookloans() {
        global $pdo;
        $stmt = $pdo->prepare("SELECT b.* FROM BookLoans bl JOIN Books b ON bl.book_id = b.book_id WHERE bl.member_id = ? AND bl.return_date IS NULL");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
}
?>