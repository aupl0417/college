<?php

class adjustLevel_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);			
    }
    
    function run() {
		$tid = $this->options['id'];
		
		if(F::isEmpty($tid)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));
		    exit;
		}
		$data = array(
		    'te_level'  => $this->options['teacherLevel'] + 0,
		    'te_reason' => $this->options['reason']
		); 
		
		$db = new MySql();
		$res = $db->update('tang_teacher', $data, 'te_userId="' . $tid . '"');
		
		if(!$res){
		    $this->show(message::getJsonMsgStruct('1002', '操作失败'));
		    exit;
		}
		
		$this->show(message::getJsonMsgStruct('1001', '操作成功'));
    }
    
}
