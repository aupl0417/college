<?php

class index extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040701]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50040701,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
        $db  = new MySql();
		
		$condition = $db->getAll('select at_key as id,at_value as name from tang_attrib where at_type=8');
		$certType  = $db->getAll('select at_key as id,at_value as name from tang_attrib where at_type=7');
		
        $this->setLoopData('certType', $certType);
        $this->setLoopData('condition', $condition);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
