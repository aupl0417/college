<?php

class changeStatus_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040502]);			
    }
    
    function run() {
		
		if(F::isEmpty($this->options['id'])){ 
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$cid   = $this->options['id'] + 0;
		$state = $this->options['state'] + 0;
		
		$data['tsi_status'] = $state == 0 ? 1 : 0;
		
		$db = new MySql();
	    $result = $db->update('tang_teacher_student_interaction', $data, 'tsi_id="' . $cid . '"');
	    
	    $data = array(
	        'state' => $state == 0 ? 1 : 0,
	        'msg'   => $state == 0 ? '显示' : '隐藏'
	    );
	    
	    $this->show(message::getJsonMsgStruct('1001', $data));exit;
		    
		
    }
}
