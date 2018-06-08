<?php

class trainRecord extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);
    }
	
    function run() {
		$id = $this->options['id'];//教师id
		
		$data = array(
		    'code'          => 50030101,
		    'teacherId'     => $id
		);
		
		$db = new MySql();
		$result = $db->getAll('select cta_startTime,cta_endTime from tang_class_table where cta_teacherId="' . $id . '"');
// 		dump($result);die;
		
		$sum = 0;
		if($result){
		    
		    foreach ($result as $key=>$val){
		        $sum += (strtotime($val['cta_endTime']) - strtotime($val['cta_startTime']));
		    }
		}
		$data['teachHours'] = round($sum / (60 * 60), 1);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
