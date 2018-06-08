<?php

class examPaper extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040302]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50040302,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
        $sql = "select co_id, co_name from tang_course";//暂时全部选择，以后根据所在分院及下属分院来获取
        $db  = new MySql();
        $courseList = $db->getAll($sql);
        
        $this->setLoopData('courseList', $courseList);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
