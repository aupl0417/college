<?php

class documentCategory  extends guest {

    function __construct($options) {        		
        parent::__construct($options, [60110]);			
    }
    function run() {
        $this->setTempAndData();
        $this->show();
    }
}
