<?php

class appManagerNull extends guest {

    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
    }

    function run() {

		$this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');

        $db = new MySql();

        $this->setTempAndData('appManagerNull/appManagerNull');
        $this->show();
    }


}