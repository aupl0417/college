<?php

class share_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);			
    }
    
    function run() {
		
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$cid = $this->options['id'] + 0;
		$type = $this->options['type'];
		
		$data = array(
		    'cr_isPublic' => intval($type) == 0 ? 1 : 0,
		);
		
		$db = new MySql();
		$result = $db->update('tang_course_resource', $data, 'cr_id="' . $cid . '"');
		
		$this->show(message::getJsonMsgStruct('1001',  intval($type) == 0 ? '共享成功' : '取消共享成功'));
    }
}
