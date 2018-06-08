<?php

class import extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50020102]);			
    }
    function run() {
		
		$data = array(
		    'code'          => 50020102,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		$result = array();
		$this->setLoopData('userInfo', $result);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
