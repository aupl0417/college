<?php

class share_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040501,50040502]);			
    }
    
    function run() {
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		if(isset($this->options['action'])){
		    $prefix = 'tsi';
		    $table  = 'tang_teacher_student_interaction';
		}else {
		    $prefix = 'tc';
		    $table  = 'tang_teacher_comment';
		}
		
		$cid = $this->options['id'] + 0;
		$type = $this->options['type'] + 0;
		
		$data = array(
		    $prefix . '_isPublic' => intval($type) == 0 ? 1 : 0,
		);
		
		$db = new MySql();
		$result = $db->update($table, $data, $prefix . '_id="' . $cid . '"');
		
		$this->show(message::getJsonMsgStruct('1001',  intval($type) == 0 ? '共享成功' : '取消共享成功'));
    }
}
