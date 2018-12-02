<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

class LeasesPresenter extends BasePresenter {

    public $database;
    private $id;

    public function renderDefault() {
        if (!$this->getUser()->isInRole("administration")) {
            $this->template->LeasesBooks = $this->database->query("SELECT l.lease_id, b.book_name, b.book_number, g.genre_name, u.user_name, u.user_surname, DATE_FORMAT(until,'%d.%m.%Y') as until, l.until < now() as late FROM books b JOIN leases l ON l.book = b.book_id JOIN genres g ON g.genre_id = b.genre JOIN users u ON u.user_id = l.user WHERE u.user_id AND l.is_returned=0");
        }
    }

    public function handleDelete($id) {

        $leases = $this->database->query("select * from leases WHERE lease_id  = ?", $id)->fetch();
        $this->database->query('UPDATE leases SET is_returned=1 WHERE lease_id =?', $leases["lease_id"]);
        $this->database->query('UPDATE books SET is_available=1 WHERE book_id=?', $leases["book"]);
    }

    public function getUsers() {
        $result = $this->database->query("SELECT user_id, user_name, user_surname, concat(user_name, ' ', user_surname) as fullname from users");
        return $result;
    }

    public function getBooks() {
        $result = $this->database->query("SELECT book_id, book_name, book_number, concat(book_name, '-', book_number) as book from books WHERE is_available=1");
        return $result;
    }
    
    public function getHistory() {
        $result = $this->database->query("SELECT b.book_name, g.genre_name, b.author_name, b.author_surname FROM books b JOIN leases l ON l.book = book_id JOIN genres g ON g.genre_id WHERE user_id = ? AND l.is_returned=1");
        return $result;
    }

    public function createComponentCreateLeaseForm() {
        if ($this->getUser()->isInRole("administrator")) {
            $form = new UI\Form();

            $users = [];
            foreach ($this->getUsers() as $user) {
                $users[$user->user_id] = $user->fullname;
            }

            $books = [];
            foreach ($this->getBooks() as $book) {
                $books[$book->book_id] = $book->book;
            }
            $renderer = $form->getRenderer();
            $renderer->wrappers['controls']['container'] = null;
            $renderer->wrappers['pair']['container'] = 'div class="material"';
            $renderer->wrappers['label']['container'] = null;
            $renderer->wrappers['control']['container'] = null;

            $form->addSelect('user', 'Vyberte uživatele', $users)
                    ->setRequired('Vyberte uživatelské jméno')
                    ->setAttribute('class', 'form-control');

            $form->addSelect('book', 'Vyber knihu', $books)
                    ->setRequired('Vyberte knihu')
                    ->setAttribute('class', 'form-control');

            $form->addText('until', 'Datum')
                    ->setRequired('Zadejte datum do kdy chcete knihu půjčit')
                    ->setDefaultValue('yyyy-mm-dd')
                    ->addRule($form::PATTERN, 'Zadali jste datum ve špatném formátu', '([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))')
                    ->setAttribute('class', 'form-control');

            $form->addSubmit('send', 'Přidat výpůjčku')
                    ->setAttribute('class', 'btn btn-primary');
            $form->onSuccess[] = [$this, 'AddLease'];

            return $form;
        }
    }

    public function AddLease(UI\Form $form, Nette\Utils\ArrayHash $values) {
        try {
            $this->database->query('INSERT INTO leases(user ,book, until, is_returned) VALUES(?, ?, ?, 0)', $values['user'], $values['book'], $values['until']);
            $this->database->query('UPDATE books SET', [
                'is_available' => 0,
                    ], 'WHERE book_id = ?', $values['book']);
            $this->redirect('Leases:');
            $this->flashMessage('Výpujčka byla vytvořena');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('xx');
        }
    }

    public function actionEditLease($id) {
        $this->id = $id;
    }

    public function EditLeaseForm(UI\Form $form, $values) {
        $result = $this->database->query('UPDATE `leases` SET', [
            'until' => $values->until,
                ], 'WHERE  `lease_id` = ?', $this->id);
        $this->flashMessage('Událost byla upravena.');
        $this->redirect("Leases:");
    }

    protected function createComponentEditLeaseForm() {
        $form = new UI\Form;

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="material"';
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;

        $defaults = $this->database->query("SELECT concat(user_name, ' ', user_surname) as fullname, b.book_number, l.user, l.until, b.book_name FROM books b JOIN leases l ON l.book = b.book_id JOIN users u ON u.user_id = l.user where lease_id = ?", $this->id)->fetch();
        $form->addText('book_name', 'Název knihy:')
                ->setAttribute('class', 'form-control disabled text-danger');
        $form->addText('book_number', 'Číslo knihy:')
                ->setAttribute('class', 'form-control disabled text-danger');
        $form->addText('fullname', 'Uživatel:')
                ->setAttribute('class', 'form-control disabled text-danger');
        $form->addText('until', 'Datum:')
                ->setAttribute('class', 'form-control')
                ->setType('date');
        $form->setDefaults($defaults);
        $form->addSubmit('EditLease', 'Upravit výpůjčku')
                ->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = [$this, 'EditLeaseForm'];
        return $form;
    }

}
