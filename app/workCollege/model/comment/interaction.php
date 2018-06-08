<?php

class interaction extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040502]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50040502,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
        $sql = "select co_id as id, co_name as name from tang_course";//暂时全部选择，以后根据所在分院及下属分院来获取
        $db  = new MySql();
        $courseList = $db->getAll($sql);
        
        $this->setLoopData('courseList', $courseList);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
