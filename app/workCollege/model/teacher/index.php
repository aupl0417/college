<?php

class index extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50030101,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
		$db = new MySql();
		$teacherLevel = $db->getAll("select tl_id,tl_name from tang_teacher_level");//讲师级别
		
		$this->setLoopData('teacherLevel', $teacherLevel);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show(); 
    }
}
