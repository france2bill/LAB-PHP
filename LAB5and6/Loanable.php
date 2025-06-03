<?php
interface Loanable {
    public function borrowBook($id);
    public function returnBook($id);
}
?>