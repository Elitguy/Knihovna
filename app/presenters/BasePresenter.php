<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette;

class BasePresenter extends Presenter {

    public $database;

    public function __construct(Nette\Database\Connection $database) {
        $this->database = $database;
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->MyNews = $this->database->query("SELECT  COUNT(news_name) as count, news_name, DATE_FORMAT(news_date,'%d.%m.%Y') as news_date FROM news ORDER BY `news_id` DESC");
        $this->template->MyEvents = $this->database->query("SELECT COUNT(event_name) as count, event_name, DATE_FORMAT(event_date,'%d.%m.%Y') as event_date FROM events ORDER BY `event_id` DESC");
        $this->template->Late = $this->database->query("SELECT COUNT(until) as late FROM leases WHERE until < now() AND user=? AND is_returned =0", $this->getUser()->getId());
    }
}

?>
