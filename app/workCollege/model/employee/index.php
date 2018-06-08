<?php

class index extends worker {

    function __construct($options) {        		
        parent::__construct($options, [500501]);			
    }
    function run() {
		$info = array();
		$data = array(
			'jsData' => json_encode($info),
		    'code'   => 500501,
			'tempId' => 'temp_'.F::getGID(),	
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
