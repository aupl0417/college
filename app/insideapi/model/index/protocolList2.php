<?php

class protocolList2 extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

        $this->setTempAndData('protocolList2/protocolList2');
		
        $this->show();
    }

}