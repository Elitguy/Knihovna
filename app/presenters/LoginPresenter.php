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
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="material"';
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;

        $form->addText('student_email', 'Email:')
                ->setAttribute('class', 'form-control');
        $form->addPassword('password', 'Heslo:')
                ->setAttribute('class', 'form-control');
        $form->addSubmit('login', 'Přihlásit se')
                ->setAttribute('class', 'btn btn-primary');
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }

    public function loginFormSucceeded(UI\Form $form, $values) {
        try {
            $this->getUser()->login($values->student_email, $values->password);
            $this->flashMessage('Byl jste úspěšně přihlášen.');
            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $this->flashMessage($e->getMessage());
        }
    }

    public function actionLogout() {
        $this->getUser()->logout();
        $this->redirect('Homepage:');
    }

}
