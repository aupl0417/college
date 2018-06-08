<?php

class setLevel extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030103]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50030103,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show(); 
    }
}
