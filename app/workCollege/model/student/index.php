<?php

class index extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50020101]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50020101,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
