<?php

class editTaxCode extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60127]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $code = $db->getField("select u_comTaxCode from t_user where u_id='".$id."'");
        $data = array(
            'id'   => $id,
            'code' => $code,
        );
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
