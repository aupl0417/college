<?php

class addPromotingFactor extends worker {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [40702]);	
		$this->db = new MySql();			
    }
    function run() {
		$options = $this->options;
		$type = (isset($this->options['type']) && F::isNotNull($this->options['type'])) ? $this->options['type'] : 'factor_given';
		$sql = "SELECT pf_memo FROM `t_promoting_factor` WHERE pf_type='".$type."'";
		$typeName = $this->db->getField($sql);
		$jsData = ['type' => $type];
		//print_r(microtime());
		//echo F::getMicrotime();
		$data = array(
			'jsData' 	=> json_encode($jsData),
			'code'   	=> 40702,
			'type'	    => $type,
			'typeName'	=> $typeName,
			'time'		=> date('Y-m-d H:i:s', time()+60)
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
	
}
