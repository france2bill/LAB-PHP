<?php
interface Loanable {
    public function borrowBook($memberId);
    public function returnBook($memberId);
}
?>