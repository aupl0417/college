<?php

class view extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030103]);
    }
	
    function run() {
		$id = $this->options['id'] + 0;
		
		$data = array(
		    'code'          => 50030103,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
		$db = new MySql();
		$courseLevel = array(1 => '初级', 2 => '中级课及以下', 3 => '高级课及以下', 4 => '其它课', 5 => '所有等级');
		$courseType  = array('公开课', '定制课', '其它');
		$teacherLevel = $db->getRow("select tl_id,tl_name,tl_badgeName,tl_logo,tl_courseLevel,tl_courseType,tl_condition from tang_teacher_level where tl_id='" . $id . "'");//讲师级别
		
	    $teacherLevel['tl_courseLevel'] = $courseLevel[$teacherLevel['tl_courseLevel']];
	    $teacherLevel['tl_courseType']  = $courseType[$teacherLevel['tl_courseType']];
	    $teacherLevel['tl_logo']  = empty($teacherLevel['tl_logo']) ? '{_TEMP_PUBLIC_}/images/none.png' : 'https://image.dttx.com/v1/tfs/' . $teacherLevel['tl_logo'];
		
		$data = array_merge($data, $teacherLevel); 
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
