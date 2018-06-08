<?php

class delLevel_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030103]);			
    }
    
    function run() {
		$id = $this->options['id'];
		
		if(F::isEmpty($id)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));
		}
		
		$db = new MySql();
		$result = $db->delete('tang_teacher_level', 'tl_id="' . $id . '"'); 
		!$result && die($this->show(message::getJsonMsgStruct('1002', '删除失败')));
		
		$this->show(message::getJsonMsgStruct('1001'));
    }
}
