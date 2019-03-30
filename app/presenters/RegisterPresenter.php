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
        $form->addText('user_name')
                ->setRequired('Zadejte prosím jméno.')
                ->setAttribute('placeholder', 'Jméno');
        $form->addText('user_surname')
                ->setRequired('Zadejte prosím příjmení.')
                ->setAttribute('placeholder', 'Příjmení');
        $form->addText('user_email')
                ->setRequired('Zadejte prosím email.')
                ->setAttribute('placeholder', 'Email')
                ->addRule($form::EMAIL, 'Email nemá správný formát.');
        $form->addPassword('password')
                ->setAttribute('placeholder', 'Heslo')
                ->setRequired('Zadejte prosím heslo.');
        $form->addPassword('password2', 'Heslo znovu: ')
                ->setAttribute('placeholder', 'Ověření hesla')
                ->setRequired('Zadejte prosím ověření hesla.')
                ->addConditionOn($form['password'], $form::VALID)
                ->addRule($form::EQUAL, 'Hesla se neshodují.', $form['password']);
        $form->addSubmit('register', 'Zaregistrovat se')
                ->setAttribute('class', 'btn btn-primary btn-text');
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
        $this->redirect('Login:');
    }

}
