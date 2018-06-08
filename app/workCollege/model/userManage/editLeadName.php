<?php

class editLeadName extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60128]);			
    }
    function run() {
//         dump($this->options);die;
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $name = $db->getField("select u_comLeadName from t_user where u_id='".$id."'");
        $data = array(
            'id'  => $id,
            'name' => $name,
        );
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
