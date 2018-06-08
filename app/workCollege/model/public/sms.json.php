<?php

class sms_json extends worker {

    function run() {		
		if(!isset($this->options['mobile'])){
			$this->show(message::getJsonMsgStruct('1002', '手机号码错误'));
		}
		if(!$_SESSION['userID']){
			return false;
		}
		log::writeLogMongo(99996, 'sms', $this->options['mobile'], array_merge($this->options,$_SESSION));
		$sms = new sms();		
		if($sms->SendValidateSMS($this->options['mobile'])){
			$this->show(message::getJsonMsgStruct('1001', '短信已发送至您的手机，请注意查收。'));
		}else{
			$this->show(message::getJsonMsgStruct('1002', '发送消息失败'));
		}	
		//echo SUBDOMAIN;		
    }

}
