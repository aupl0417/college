<?php

class del_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040701]);			
    }
    
    function run() {
		
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$db = new MySql();
		$cid = $this->options['id'] + 0;
	    $result = $db->delete('tang_certificate', 'tce_id="' . $cid . '"');
	    if(!$result){
	        die($this->show(message::getJsonMsgStruct('1001', '删除失败')));
	    }
	    
	    $this->show(message::getJsonMsgStruct('1001', '删除成功'));exit;
    }
}
