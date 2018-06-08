<?php

class adjustLevel extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);
    }
	
    function run() {
		$id = $this->options['id'];
		$data = array(
		    'id' => $id,
		    'code'          => 50030101,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		$sql = "select id,username,te_level,tl_name from tang_ucenter_member 
		       LEFT JOIN tang_teacher on id=te_userId 
		       LEFT JOIN tang_teacher_level on te_level=tl_id 
		       where id='{$id}'";
		$db = new MySql();
		
		$result = $db->getRow($sql);
		$data = array_merge($data, $result);
		
		$teacherLevel = $db->getAll("select tl_id,tl_name from tang_teacher_level");//讲师级别
		$data['teacherLevel'] = $this->getSelectString($teacherLevel, 'tl_id', 'tl_name');
		$classTableTime = $db->getAll('select cta_startTime,cta_endTime from tang_class_table where cta_teacherId="'.$id.'"');
		
		$sum = 0;
		foreach ($classTableTime as $key=>$val){
		    $sum += (strtotime($val['cta_endTime']) - strtotime($val['cta_startTime']));
		}
		$data['teachHours'] = round($sum / (60 * 60), 1);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
    
    private function getSelectString($data, $keyId, $keyName){
        if(!is_array($data)){
            return $data;
        }
    
        $string = '';
        foreach($data as $key=>$val){
            $string .= '<option value="'.$val[$keyId].'">' . $val[$keyName] . '</option>';
        }
    
        return $string;
    }
}
