<?php

class share_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040301,50040302]);			
    }
    
    function run() {
		$cid = $this->options['id'];
		$type = $this->options['type'];
		
		if(F::isEmpty($cid)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		$tableString = '';
		if(isset($this->options['action'])){
		    $prefix = 'cep';
		    $tableString = '_paper';
		}else {
		    $prefix = 'cre';
		}
		
		$data = array(
		    $prefix . '_isPublic' => intval($type) == 0 ? 1 : 0,
		);
		
		$db = new MySql();
		$result = $db->update('tang_course_exam' . $tableString, $data, $prefix . '_id="' . $cid . '"');
		
		$this->show(message::getJsonMsgStruct('1001',  intval($type) == 0 ? '共享成功' : '取消共享成功'));
    }
}
