<?php

class del_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040301]);			
    }
    
    function run() {
		
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		$cid = $this->options['id'] + 0;
		
		$tableString = '';
		if(isset($this->options['type']) && $this->options['type'] == 1){
		    $prefix = 'cep';
		    $tableString = '_paper';
		}else {
		    $prefix = 'cre';
		}
		$db = new MySql();
	    $result = $db->delete('tang_course_exam' . $tableString, $prefix . "_id={$cid}");
	    
        !$result && exit($this->show(message::getJsonMsgStruct('1002', '删除失败')));
		$this->show(message::getJsonMsgStruct('1001', '删除成功'));exit;
		    
    }
}
