<?php

namespace App\Presenters;

use Nette;

class BooksPresenter extends BasePresenter {

    public $database;

    public function renderDefault() {
        $this->template->AllBooks = $this->database->query("SELECT b.book_name, b.author_name, b.author_surname, b.book_number, g.genre_name FROM genres g JOIN books b ON b.genre = g.genre_id WHERE b.is_available=1 ORDER BY `book_id` DESC");
    }
}
