<?php

class getUser_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50020102]); 
    }
    
    function run() {
        if(isset($this->options['username']) && !empty($this->options['username'])){
		    $param = $this->options['username'];
		}else if(isset($this->options['mobile']) && !empty($this->options['mobile'])){
		    $param = $this->options['mobile'];
		}else {
		    exit($this->show(message::getJsonMsgStruct('1001', array())));
		}
		
		
	    $params['input'] = $param;
        $path = '/user/getUser';
        $sdk  = new openSdk();
        $result = $sdk->request($params, $path);
	    
        !is_array($result) && die($this->show(message::getJsonMsgStruct('1002', $result)));
        
        $userInfo = $result['info'];
        if($userInfo['type']){
            $userInfo['name'] = empty($userInfo['comLegalName']) ? (empty($userInfo['comLeadName']) ? $userInfo['comLeadName'] : '') : $userInfo['comLegalName'];
            if(!empty($userInfo['comLeadName']) && !empty($userInfo['leadCardNum'])){
                $userInfo['certNum'] = $userInfo['leadCardNum'];
            }else if(!empty($userInfo['comLegalName']) && !empty($userInfo['legalCardNum'])) {
                $userInfo['certNum'] = $userInfo['legalCardNum'];
            }
        }
        
	    exit($this->show(message::getJsonMsgStruct('1001', $userInfo)));
		
    }
}
