<?php

class del_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040401]);			
    }
    
    function run() { 
		$cid = intval($this->options['id']);
		
		if(F::isEmpty($cid)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$db = new MySql();
		$state = $db->getField('select br_state from tang_branch where br_id="' . $cid . '"');
		if($state == 1){
		    die($this->show(message::getJsonMsgStruct('1002', '该机构已通过审核，不能删除')));
		}
		
 
	    $result = $db->delete('tang_branch', 'br_id="' . $cid .'"');
 
	    if(!$result){
	        $this->show(message::getJsonMsgStruct('1002', '删除失败'));exit;
	    }
	    
	    $this->show(message::getJsonMsgStruct('1001', '删除成功'));exit;
    }
}
