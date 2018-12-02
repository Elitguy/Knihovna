<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI;
use Nette\Security as NS;

class RegisterPresenter extends BasePresenter {

    public $database;

    function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    protected function startup() {
        parent::startup();
        $user = $this->getUser();
        if ($user->isLoggedIn()) {
            throw new Nette\Application\ForbiddenRequestException;
        }
    }

    protected function createComponentRegisterForm() {
        $form = new UI\Form;
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="material"';
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;
        
        $form->addText('user_name', 'Jméno:')
                ->setRequired('Zadejte prosím jméno.')
                ->setAttribute('class', 'form-control');
        $form->addText('user_surname', 'Příjmení:')
                ->setRequired('Zadejte prosím příjmení.')
                ->setAttribute('class', 'form-control');
        $form->addText('user_email', 'Email:')
                ->setRequired('Zadejte prosím email.')
                ->setAttribute('class', 'form-control');
        $form->addPassword('password', 'Heslo:')
                ->setRequired('Zadejte prosím heslo')
                ->setAttribute('class', 'form-control');
        $form->addSubmit('register', 'Zaregistrovat se')
                ->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = [$this, 'registerFormSucceeded'];
        return $form;
    }

    public function registerFormSucceeded(UI\Form $form, $values) {
        $this->database->table('users')->insert([
            'user_name' => $values->user_name,
            'user_surname' => $values->user_surname,
            'user_email' => $values->user_email,
            'password' => NS\Passwords::hash($values->password),
            'role' => $this->getUser()->authenticatedRole
        ]);
        $this->flashMessage('Byl jste úspěšně zaregistrován.');
        $this->redirect('Homepage:');
    }

}
