<?php

class editLegal extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60120]);			
    }
    function run() {
//         dump($this->options);die;
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $legal = $db->getField("select u_comLegalName from t_user where u_id='".$id."'");
        $data = array(
            'id'  => $id,
            'legal' => $legal,
        );
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
