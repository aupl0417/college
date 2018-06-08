<?php

class add extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030102]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50030102,
			'tempId'		=> 'temp_'.F::getGID(),
		); 
		
		$db = new MySql();
		$teacherLevel = $db->getAll("select tl_id,tl_name from tang_teacher_level");//讲师级别
		$courseType  = array('公开课', '定制课', '其它');
		$branchList    = $db->getAll('select br_id,br_name from tang_branch');
		
		$string = '';
		foreach($teacherLevel as $key=>$val){
            $string .= '<option value="'.$val['tl_id'].'">' . $val['tl_name'] . '</option>';
		}
		
		$branchString = '';
		foreach ($branchList as $key=>$val) {
		    $branchString .= '<option value="'.$val['br_id'].'">' . $val['br_name'] . '</option>';
		}
		
		$data['teacherLevel'] = $string;
		$data['gradeList'] = F::array2Options($courseType);
		$data['branchList'] = $branchString;
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
