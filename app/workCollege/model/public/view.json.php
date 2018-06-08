<?php

class view_json extends worker {

    function __construct($options) {
        parent::__construct($options, []);
    }
    function run() {	
		$sm_id = isset($this->options['id']) ? F::fmtNum($this->options['id']) : 0;
		if($sm_id){
			$sitemsg = new sitemsg();
			$result = $sitemsg->read($sm_id, $_SESSION['userID']);
			if($result){
				$this->show(message::getJsonMsgStruct('1001', $result));
			}else{
				$this->show(message::getJsonMsgStruct('1002'));
			}
			
		}else{
			$this->show(message::getJsonMsgStruct('1002'));
		}
    }

}
