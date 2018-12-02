<?php

namespace App\Presenters;

use Nette;

class NewsPresenter extends BasePresenter
{   
      public $database;

  public function renderDefault() {
        if (!$this->getUser()->isInRole("authenticated")) {
            $this->flashMessage("x");
        }
    }
}
