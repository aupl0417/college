<?php

class promotingFactor extends worker {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [40701]);	
		$this->db = new MySql();			
    }
    function run() {
		$options = $this->options;
		$type = isset($options['type']) ? (F::fmtNum($options['type']) - 0) : 0;
		
		$jsData = ['type' => $type];
		$data = array(
			'jsData' => json_encode($jsData),
			'code'   => 40701,
			'type'   => $type,
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
	
}
