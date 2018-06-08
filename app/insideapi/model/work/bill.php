<?php

class bill extends guest {
    function __construct($options) {        		
        parent::__construct($options, [8]);
    }

    function run() {
        $this->setTempAndData();
        $this->show();
    }

}
