<?php

class signUp extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50020101]);
    }
	
    function run() {
        
		$sql = "select id,username,mobile,cs_classId,cl_gradeId,(select gr_name from tang_grade where gr_id=cl_gradeId limit 1 order by gr_id desc) as gradeName from tang_ucenter_member 
		    left join tang_class_student on cs_studentId=id 
		    left join tang_class on cl_id=cs_classId 
		    left join tang_grade on cl_gradeId=gr_id 
		    where id='". $this->options['id'] . "'";
		
		$db = new MySql();
		$data = $db->getRow($sql);
		$classStudent = $db->getRow('select * from (select sum(cs_score) as totalScore, count(cs_id) as classCount from tang_class_student where cs_studentId="'.$this->options['id'].'") as a');
		$data = array_merge($data, $classStudent);
		
		$certificateInfo = $db->getAll('select se_id,se_createTime,cce_name as certificateName from tang_student_certificate LEFT JOIN tang_certificate_templet on se_certificateTempId=cce_id where se_studentId="' . $this->options['id'] . '"');
        
		$this->setLoopData('studentCertificate', $certificateInfo);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
