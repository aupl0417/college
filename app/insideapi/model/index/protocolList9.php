<?php

class protocolList9 extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

		$this->foot = F::readFile(APPROOT. '/template/cn/api/share/footProtocolList.html');

        $this->setTempAndData('protocolList9/protocolList9');
		
        $this->show();
    }

}