<?php

class editComThree extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60125]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $three = $db->getField("select u_companyThree from t_user where u_id='".$id."'");
        if ($three){
            $three = "<option value='1' selected>是</option><option value='0'>否</option>";
        }else{
            $three = "<option value='1'>是</option><option value='0' selected>否</option>";
        }
        $data = array(
            'id'   => $id,
            'three'=> $three,
        );
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
