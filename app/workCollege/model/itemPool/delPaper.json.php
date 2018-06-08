<?php

class delPaper_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040302]);			
    }
    
    function run() {
		
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		$cid = $this->options['id'] + 0;
		
		$db = new MySql();
	    $result = $db->delete(tang_course_exam_paper, 'cep_id="' . $cid . '"');
	    if(!$result){
	        $this->show(message::getJsonMsgStruct('1002', '删除失败'));exit;
	    }
		    
        $this->show(message::getJsonMsgStruct('1001', '删除成功'));exit;
		    
    }
}
