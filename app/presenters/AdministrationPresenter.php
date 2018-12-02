<?php

namespace App\Presenters;

use Nette\Application\UI;
use Nette;

class AdministrationPresenter extends BasePresenter {

    public $database;
    private $id;

    public function renderDefault() {
        if ($this->getUser()->isInRole("administrator")) {
            $this->template->Administrace = $this->database->query("SELECT user_name, user_surname FROM users WHERE user_id=?", $this->getUser()->getId());
            $this->flashMessage("x");
        }
    }

//vytvořit knihu začátek
    public function createComponentCreateBookForm() {
        $genres = [];
        foreach ($this->GetGenre() as $g) {
            $genres[$g->genre_id] = $g->genre_name;
        }
        if ($this->getUser()->isInRole("administrator")) {
            $form = new UI\Form();

            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = null;
            $renderer->wrappers['pair']['container'] = 'div class="material"';
            $renderer->wrappers['label']['container'] = null;
            $renderer->wrappers['control']['container'] = null;

            $form->addText('book_name', 'Název knihy')
                    ->setRequired('Zadejte prosím název knihy')
                    ->setAttribute('class', 'form-control');
            $form->addText('author_name', 'Jméno autora')
                    ->setRequired('Jméno autora')
                    ->setAttribute('class', 'form-control');
            $form->addText('author_surname', 'Příjmení autora')
                    ->setRequired('Příjmení autora')
                    ->setAttribute('class', 'form-control');
            $form->addText('book_number', 'Číslo knihy')
                    ->setRequired('Číslo knihy')
                    ->setAttribute('class', 'form-control');
            $form->addSelect('genre', 'Žánr', $genres)
                    ->setRequired('Žánr')
                    ->setAttribute('class', 'form-control');

            $form->addSubmit('send', 'Přidat knihu')
                    ->setAttribute('class', 'btn btn-primary');
            $form->onSuccess[] = [$this, 'addBook'];
            return $form;
        }
    }

    public function GetGenre() {
        $result = $this->database->query("SELECT * FROM `genres`");
        return $result;
    }

    public function addBook(UI\Form $form, Nette\Utils\ArrayHash $values) {
        try {
            $this->database->query('INSERT INTO books(book_name ,author_name, author_surname, book_number, genre) VALUES(?, ?, ?, ?, ?)', $values['book_name'], $values['author_name'], $values['author_surname'], $values['book_number'], $values['genre']);
            $this->redirect('Administration:bookManagement');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nepovedlo se přidat knihu.');
        }
    }

//vytvořit knihu konec
//smazat knihu začátek
    public function handleDelete($id) {
        $book = $this->database->query("select * from books WHERE book_id  = ?", $id)->fetch();
        $new = $this->database->query("select * from news WHERE news_id  = ?", $id)->fetch();
        $event = $this->database->query("select * from events WHERE event_id  = ?", $id)->fetch();
        if ($book) {
            $this->database->query("delete from books WHERE book_id  = ?", $id);
            $this->redirect("Administration:bookManagement");
        } else if ($new) {
            $this->database->query("delete from news WHERE news_id  = ?", $id);
            $this->redirect("Administration:newManagement");
        } else if ($event) {
            $this->database->query("delete from events WHERE event_id  = ?", $id);
            $this->redirect("Administration:eventManagement");
        }
    }

//smazat knihu konec 

    public function GetBook() {
        $result = $this->database->query("SELECT * FROM books WHERE is_available=1");
        return $result;
    }

//výpis knih začátek
    public function renderBookManagement() {
        $this->template->books = $this->Books();
    }

    public function Books() {
        if ($this->getUser()->isInRole("administrator")) {
            $result = $this->database->query("SELECT b.book_id, b.book_name, b.author_name, b.author_surname, b.book_number, g.genre_name FROM genres g JOIN books b ON b.genre = g.genre_id WHERE b.is_available=1 ORDER BY `book_id` DESC");
            return $result;
        }
    }

//výpis knih konec
//vytořit knihu začátek 
    public function actionEditBook($id) {
        $this->id = $id;
    }

    public function EditBookForm(UI\Form $form, $values) {
        $result = $this->database->query('UPDATE `books` SET', [
            'book_name' => $values->book_name,
            'author_name' => $values->author_name,
            'author_surname' => $values->author_surname,
            'book_number' => $values->book_number,
            'genre' => $values->genre,
                ], 'WHERE  `book_id` = ?', $this->id);
        $this->flashMessage('Kniha byla upravena');
        $this->redirect("Administration:bookManagement");
    }

    protected function createComponentEditBookForm() {
        $genres = [];
        foreach ($this->GetGenre() as $g) {
            $genres[$g->genre_id] = $g->genre_name;
        }
        $form = new UI\Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="material"';
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;
        $defaults = $this->database->query('SELECT book_name, genre, author_name, author_surname, book_number FROM books where book_id = ?', $this->id)->fetch();

        $form->addText('book_name', "Název knihy:")
                ->setAttribute('class', 'form-control');
        $form->addText('author_name', 'Jméno autora:')
                ->setAttribute('class', 'form-control');
        $form->addText('author_surname', 'Příjmení autora:')
                ->setAttribute('class', 'form-control');
        $form->addText('book_number', 'Ozačení knihy:')
                ->setAttribute('class', 'form-control');
        $form->addSelect('genre', 'Žánr', $genres)
                ->setAttribute('class', 'form-control');
        $form->setDefaults($defaults);
        $form->addSubmit('EditBook', 'Upravit knihu')
                ->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = [$this, 'EditBookForm'];
        return $form;
    }

    //vypis akcí začátek  
    public function renderEventManagement() {
        $this->template->event = $this->Events();
    }

    public function Events() {
        if ($this->getUser()->isInRole("administrator")) {
            $result = $this->database->query("SELECT event_id, event_content, event_name, DATE_FORMAT(event_date,'%d.%m.%Y') as event_date FROM events ORDER BY `event_id` DESC");
            return $result;
        }
    }

    //výpis akcí konec
    //výpis novicek začátek   
    public function renderNewManagement() {
        $this->template->new = $this->News();
    }

    public function News() {
        if ($this->getUser()->isInRole("administrator")) {
            $result = $this->database->query("SELECT news_id, news_name, news_content, DATE_FORMAT(news_date,'%d.%m.%Y') as news_date FROM news ORDER BY `news_id` DESC");
            return $result;
        }
    }

//výpis novinek konec
//vytvořit akci začátek
    public function createComponentCreateNewForm() {

        if ($this->getUser()->isInRole("administrator")) {
            $form = new UI\Form();

            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = null;
            $renderer->wrappers['pair']['container'] = 'div class="material"';
            $renderer->wrappers['label']['container'] = null;
            $renderer->wrappers['control']['container'] = null;

            $form->addText('news_name', 'Název novinky')
                    ->setRequired('Zadejte prosím název novinky')
                    ->setAttribute('class', 'form-control');
            $form->addTextArea('news_content', 'Obsah novinky')
                    ->setRequired('Obsah novinky')
                    ->setAttribute('class', 'form-control');
            $form->addText('news_date', 'Zadej datum')
                    ->setRequired('Datum novinky')
                    ->setAttribute('class', 'form-control');
            $form->addSubmit('send', 'Přidat novinku')
                    ->setAttribute('class', 'btn btn-primary');
            $form->onSuccess[] = [$this, 'addNew'];
            return $form;
        }
    }

    public function addNew(UI\Form $form, Nette\Utils\ArrayHash $values) {
        try {
            $this->database->query('INSERT INTO news(news_name ,news_content, news_date) VALUES(?, ?, ?)', $values['news_name'], $values['news_content'], $values['news_date']);
            $this->redirect('Administration:newManagement');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nepovedlo se přidat novinku.');
        }
    }

    public function createComponentCreateEventForm() {

        if ($this->getUser()->isInRole("administrator")) {
            $form = new UI\Form();

            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = null;
            $renderer->wrappers['pair']['container'] = 'div class="material"';
            $renderer->wrappers['label']['container'] = null;
            $renderer->wrappers['control']['container'] = null;

            $form->addText('event_name', 'Název novinky')
                    ->setRequired('Zadejte prosím název akci')
                    ->setAttribute('class', 'form-control');
            $form->addTextArea('event_content', 'Obsah akce')
                    ->setRequired('Obsah novinky')
                    ->setAttribute('class', 'form-control');
            $form->addText('event_date', 'Zadej datum')
                    ->setRequired('Datum akce')
                    ->setAttribute('class', 'form-control');
            $form->addSubmit('send', 'Přidat akci')
                    ->setAttribute('class', 'btn btn-primary');
            $form->onSuccess[] = [$this, 'addEvent'];
            return $form;
        }
    }

    public function addEvent(UI\Form $form, Nette\Utils\ArrayHash $values) {
        try {
            $this->database->query('INSERT INTO events(event_name ,event_content, event_date) VALUES(?, ?, ?)', $values['event_name'], $values['event_content'], $values['event_date']);
            $this->redirect('Administration:eventManagement');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nepovedlo se přidat akci.');
        }
    }

    public function actionEditEvent($id) {
        $this->id = $id;
    }

    public function EditEventForm(UI\Form $form, $values) {
        $result = $this->database->query('UPDATE `events` SET', [
            'event_name' => $values->event_name,
            'event_content' => $values->event_content,
            'event_date' => $values->event_date,
                ], 'WHERE  `event_id` = ?', $this->id);
        $this->flashMessage('Kniha byla upravena');
        $this->redirect("Administration:eventManagement");
    }

    protected function createComponentEditEventForm() {
        $form = new UI\Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="material"';
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;

        $defaults = $this->database->query("SELECT event_name, event_content, DATE_FORMAT(event_date,'%d.%m.%Y') as event_date FROM events where event_id = ?", $this->id)->fetch();

        $form->addText('event_name', 'Název akce:')
                ->setAttribute('class', 'form-control');
        $form->addText('event_content', 'Obsah akce:')
                ->setAttribute('class', 'form-control');
        $form->addText('event_date', 'Datum:')
                ->setAttribute('class', 'form-control');
        $form->setDefaults($defaults);
        $form->addSubmit('EditEvent', 'Upravit event')
                ->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = [$this, 'EditEventForm'];
        return $form;
    }

    public function actionEditNew($id) {
        $this->id = $id;
    }

    public function EditNewForm(UI\Form $form, $values) {
        $result = $this->database->query('UPDATE `news` SET', [
            'news_name' => $values->news_name,
            'news_content' => $values->news_content,
            'news_date' => $values->news_date,
                ], 'WHERE  `news_id` = ?', $this->id);
        $this->flashMessage('Událost byla upravena.');
        $this->redirect("Administration:newManagement");
    }

    protected function createComponentEditNewForm() {
        $form = new UI\Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="material"';
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;

        $defaults = $this->database->query("SELECT news_name, news_content, DATE_FORMAT(news_date,'%d.%m.%Y') as news_date FROM news where news_id = ?", $this->id)->fetch();
        $form->addText('news_name', 'Název akce:')
                ->setAttribute('class', 'form-control');
        $form->addText('news_content', 'Obsah akce:')
                ->setAttribute('class', 'form-control');
        $form->addText('news_date', 'Datum:')
                ->setAttribute('class', 'form-control');
        $form->setDefaults($defaults);
        $form->addSubmit('EditNew', 'Upravit event')
                ->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = [$this, 'EditNewForm'];
        return $form;
    }

    public function actionDuplicateBook($id) {
        $this->id = $id;
    }

    public function DuplicateBookForm(UI\Form $form, $values) {
        try {
            $this->database->query('INSERT INTO books(book_name ,author_name, author_surname, book_number, genre) VALUES(?, ?, ?, ?, ?)', $values['book_name'], $values['author_name'], $values['author_surname'], $values['book_number'], $values['genre']);
            $this->redirect('Administration:bookManagement');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nepovedlo se přidat knihu.');
        }
    }

    protected function createComponentDuplicateBookForm() {
        $genres = [];
        foreach ($this->GetGenre() as $g) {
            $genres[$g->genre_id] = $g->genre_name;
        }
        $form = new UI\Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="material"';
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;

        $defaults = $this->database->query('SELECT book, user, until FROM leases where lease_id = ?', $this->id)->fetch();

        $form->addText('book_name', "Název knihy:")
                ->setAttribute('class', 'form-control disabled text-danger');
        $form->addText('author_name', 'Jméno autora:')
                ->setAttribute('class', 'form-control disabled text-danger');
        $form->addText('author_surname', 'Příjmení autora:')
                ->setAttribute('class', 'form-control disabled text-danger');
        $form->addText('book_number', 'Ozačení knihy:')
                ->setAttribute('class', 'form-control');
        $form->addSelect('genre', 'Žánr', $genres)
                ->setAttribute('class', 'form-control disabled text-danger');
        $form->setDefaults($defaults);
        $form->addSubmit('DuplicateBook', 'Duplikovat')
                ->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = [$this, 'DuplicateBookForm'];
        return $form;
    }

}
