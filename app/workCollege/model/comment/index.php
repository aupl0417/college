<?php

class index extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040501]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50040501,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
        $sql = "select cl_id as id, cl_name as name from tang_class";//暂时全部选择，以后根据所在分院及下属分院来获取
        $db  = new MySql();
        $classList = $db->getAll($sql);
        
        $this->setLoopData('classList', $classList);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
