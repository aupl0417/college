<?php

class review extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030104]);
    }
	
    function run() {
		$id = $this->options['id'] + 0;
		
		$data = array(
		    'code'          => 50030104,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
		$db = new MySql();
		$field = 'tp_id as id,username,trueName,tp_teacherId,te_level,a.tl_name as oriLevel,b.tl_name as proLevel,b.tl_condition as proCondition,tp_createTime';
		$sql = "select {$field} from tang_teacher_promotion
		    left join tang_teacher on tp_teacherId=te_userId
		    left join tang_teacher_level a on te_level=a.tl_id
		    left join tang_teacher_level b on tp_applyLevelId=b.tl_id
		    left join tang_ucenter_member on tp_teacherId=id
		    where tp_id='" . $id . "'";
		
		$result = $db->getRow($sql); 
		$classTableData = $db->getAll('select cta_startTime,cta_endTime from tang_class_table where cta_teacherId="' . $result['tp_teacherId'] .'"');

		$totalSeconds = 0;
        foreach($classTableData as $key=>$val){
            $totalSeconds += (strtotime($val['cta_endTime']) - strtotime($val['cta_startTime']));
        }
        
        $result['teacherHours'] = round($totalSeconds / 3600, 1);
        $data = array_merge($data, $result);
        
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
