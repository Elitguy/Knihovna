<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

class LoginPresenter extends BasePresenter {

    protected function startup() {
        parent::startup();
        $user = $this->getUser();
        if ($user->isLoggedIn()) {
            throw new Nette\Application\ForbiddenRequestException;
        }
    }

    protected function createComponentLoginForm() {
        $form = new UI\Form;
        $form->addText('user_email')
                ->setRequired('Prosím vyplňte svůj email.')
                ->setAttribute('placeholder', 'Email');
        $form->addPassword('password')
                ->setRequired('Prosím vyplňte své heslo.')
                ->setAttribute('placeholder', 'Heslo');
        $form->addSubmit('login', 'Přihlásit se');
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }

    public function loginFormSucceeded(UI\Form $form, $values) {
        try {
            $this->getUser()->login($values->user_email, $values->password);
            $this->flashMessage('Byl jste úspěšně přihlášen.');
            if ($this->getUser()->isInRole("administrator")) {
                $this->flashMessage('Byl/a jste úspěšně přihlášen.');
                $this->redirect('Administration:');
            } else {
                $this->redirect('Profile:');
            }
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací email nebo heslo.');
        }
    }
}
