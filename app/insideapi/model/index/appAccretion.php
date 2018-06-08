<?php

class appAccretion extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

		$this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');
        if(!isset($_SESSION['userID'])){
            header('Location: http://'.INSIDEAPI.'/index/console');
            exit;
        }

        $this->setTempAndData('appAccretion/appAccretion');
        $this->show();
    }


}