<?php

class view extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50020101]);
    }
	
    function run() {
		$id = $this->options['id'];
// 		echo $id;die;
		$sql = "select id as DT_RowId,username,email,br_name as branchName,mobile from tang_ucenter_member left join tang_branch on tangCollege=br_id where identityType=0 and id='{$id}'";
		$db = new MySql();
        $result = $db->getRow($sql);
        
        $classStudent = $db->getRow('select * from (select count(*) as classCount,sum(cs_score) as totalScore from tang_class_student where cs_studentId="'.$result['DT_RowId'].'") as a');
        $result['classCount'] = $classStudent['classCount'];
        $result['totalScore'] = $classStudent['totalScore'] ? $classStudent['totalScore'] : 0;
        
		$this->setReplaceData($result);
        $this->setTempAndData();
        $this->show();
    }
}
