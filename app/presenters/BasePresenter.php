<?php

namespace App\Presenters;

use Tracy\Debugger;
use Nette\Application\UI\Presenter;
use Nette;

class BasePresenter extends Presenter {

    public $database;

    public function __construct(Nette\Database\Connection $database) {
        $this->database = $database;
    }

    public function beforeRender() {
        parent::beforeRender();
        $this->template->MyNews = $this->database->query("SELECT news_id, news_content, news_name, DATE_FORMAT(news_date,'%d.%m.%Y') as news_date FROM news ORDER BY `news_date` DESC LIMIT 3")->fetchAll();
        $this->template->MyEvents = $this->database->query("SELECT event_id, event_content, event_name, DATE_FORMAT(event_date,'%d.%m.%Y') as event_date FROM events ORDER BY `event_date` DESC LIMIT 3")->fetchAll();
        $this->template->Late = $this->database->query("SELECT COUNT(until) as late FROM leases WHERE until < now() AND user=? AND is_returned =0", $this->getUser()->getId());
    }

}

?>
