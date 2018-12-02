<?php

namespace App;
use Nette;
use Nette\Security as NS;

class UserAuthenticator implements NS\IAuthenticator
{
    public $database;

    function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    function authenticate(array $credentials)
    {
        list($user_email, $password) = $credentials;
        $row = $this->database->table('users')->where('user_email', $user_email)->select("*")->fetch();
        if (!$row) {
            throw new NS\AuthenticationException('Uživatel nebyl nenalezen');
        }

        if (!NS\Passwords::verify($password, $row->password)) {
            throw new NS\AuthenticationException('Zadali jste neplatné heslo.');
        }

        return new NS\Identity($row->user_id, $row->role, ['user_email' => $row->user_email]);
        
        
    }
}
