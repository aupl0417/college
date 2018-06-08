<?php

class addClassPicture extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        
        if(!isset($this->options['clID']) || empty($this->options['clID'])){
            die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        }
        
        $data = array(
            'code' => '500103',
            'classId' => $this->options['clID'] + 0,
            'PHPSESSID' => session_id()
        );
        
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
