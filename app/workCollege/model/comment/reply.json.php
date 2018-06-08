<?php

class reply_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040503]);			
    }
    
    function run() {
		
		
		if(F::isEmpty($this->options['fid'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
        if(F::isEmpty($this->options['reply'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		} 
		
		$fid = $this->options['fid'] + 0;
		$data = array(
		    'f_content' => $this->options['reply'],
		    'f_replyId' => $fid,
		    'f_createTime' => date('Y-m-d H:i:s'),
		    'f_type'  => 1
		);
		$db = new MySql();
	    $result = $db->insert('tang_feedback', $data);
	    !$result && die($this->show(message::getJsonMsgStruct('1002', '回复失败')));
	    $this->show(message::getJsonMsgStruct('1001', '回复成功'));
    }
}
