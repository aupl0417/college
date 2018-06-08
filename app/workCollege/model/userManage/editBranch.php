<?php

class editBranch extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60124]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $branch = $db->getField("select u_isBranch from t_user where u_id='".$id."'");
        if ($branch){
            $branch = "<option value='1' selected>是</option><option value='0'>否</option>";
        }else{
            $branch = "<option value='1'>是</option><option value='0' selected>否</option>";
        }
        $data = array(
            'id'   => $id,
            'branch'=> $branch,
        );
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
