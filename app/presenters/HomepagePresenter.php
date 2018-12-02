<?php

namespace App\Presenters;

use Nette;

class HomepagePresenter extends BasePresenter
{
  public $database;
  public function __construct(Nette\Database\Connection $database){
    $this->database = $database;
  }

}
