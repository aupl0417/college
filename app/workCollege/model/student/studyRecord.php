<?php

class studyRecord extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50020101]);
    }
	
    function run() {
		
		$sql = "select cs_id,cs_classId,cl_id,cl_name,cl_startTime,cl_endTime,cc_credit,co_name,co_content,gr_name from tang_class_student 
		    left join tang_class on cs_classId=cl_id 
		    left join tang_grade on cl_gradeId=gr_id 
		    left join tang_class_course on cl_id=cc_classId
		    left join tang_course on cc_courseId=co_id 
		    where cs_studentId='". $this->options['id'] . "'";
		
		$db = new MySql();
		$result = $db->getAll($sql);
		
		$data = array(
		    'code' => '20101',
		    'username'=>$db->getField('select username from tang_ucenter_member where id="'.$this->options['id'] . '"'),
		);
		
		$this->setLoopData('classInfo', $result);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
