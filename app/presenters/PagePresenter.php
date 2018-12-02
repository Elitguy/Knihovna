<?php

namespace App\Presenters;

use Nette;

class PagePresenter extends BasePresenter {
  public $database;

    public function actionDefault($url) {
        $this->setView($url);
    }

}
