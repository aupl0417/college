<?php

class protocolList4 extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        $this->setTempAndData('protocolList4/protocolList4');
		
        $this->show();
    }

}