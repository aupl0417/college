<?php

class del_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50020101,50020101]);
    }
    
    function run() {
		$sid = $this->options['id'];
		
		if(F::isEmpty($sid)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));
		}
		
		$db = new MySql();
		$result = $db->delete('tang_ucenter_member', 'id="' . $sid . '"');
		
		$this->show(message::getJsonMsgStruct('1001',  $result));
    }
}
