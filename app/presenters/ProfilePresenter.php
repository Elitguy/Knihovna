<?php

namespace App\Presenters;

use Nette;

class ProfilePresenter extends BasePresenter {

    public $database;

    public function renderDefault() {
        if ($this->getUser()->isInRole("authenticated")) {
            $this->template->MyProfile = $this->database->query("SELECT user_name, user_surname FROM users WHERE user_id=?", $this->getUser()->getId());
            $this->template->myLeases = $this->database->query("SELECT b.author_name, b.author_surname, b.book_name, g.genre_name, DATE_FORMAT(until,'%d.%m.%Y') as until, l.until < now() as late FROM books b JOIN leases l ON l.book = b.book_id JOIN genres g ON g.genre_id = b.genre JOIN users u ON u.user_id = l.user WHERE u.user_id=? AND l.is_returned=0", $this->getUser()->getId());          
            $this->template->myHistory = $this->database->query("SELECT b.book_name, b.author_name, b.author_surname, g.genre_name, l.until FROM books b JOIN leases l ON l.book = b.book_id JOIN genres g ON g.genre_id = b.genre JOIN users u ON u.user_id = l.user WHERE u.user_id=? AND l.is_returned=1", $this->getUser()->getId());
        }
    }
}
