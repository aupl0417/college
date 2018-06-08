<?php

class protocolList extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

        $this->setTempAndData('protocolList/protocolList');
		
        $this->show();
    }

}