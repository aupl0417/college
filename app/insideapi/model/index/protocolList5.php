<?php

class protocolList5 extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

		$this->foot = F::readFile(APPROOT. '/template/cn/api/share/footProtocolList.html');

        $this->setTempAndData('protocolList5/protocolList5');
		
        $this->show();
    }

}