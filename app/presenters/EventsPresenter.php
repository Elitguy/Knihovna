<?php

namespace App\Presenters;

use Nette;

class EventsPresenter extends BasePresenter
{   
      public $database;

  public function renderDefault() {
        if (!$this->getUser()->isInRole("authenticated")) {
            $this->flashMessage("x");
        }
    }
}
