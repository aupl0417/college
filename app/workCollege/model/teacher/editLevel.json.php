<?php

class editLevel_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030103]);			
    }
    
    function run() {
		$id = $this->options['id'];
		
		if(F::isEmpty($id)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$teacherLevelData = array(
		    'tl_name'         => $this->options['name'],
		    'tl_badgeName'    => $this->options['badgeName'],
		    'tl_courseLevel'  => $this->options['courseLevel'] + 0,
		    'tl_courseType'   => $this->options['courseType'] + 0,
		    'tl_condition'    => $this->options['condition'],
		); 
		
		$db = new MySql();
		$res = $db->update('tang_teacher_level', $teacherLevelData, "tl_id='" . intval($id) . "'");
		if($res === false){
		    $this->show(message::getJsonMsgStruct('1002',  '编辑失败'));exit;
		}
		
		$this->show(message::getJsonMsgStruct('1001',  '编辑成功'));exit;
    }
}
