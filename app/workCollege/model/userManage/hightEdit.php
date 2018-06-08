<?php

class hightEdit extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60111]);			
    }
    function run() {
//         dump($this->options);die;
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $type = $db->getRow("select u_type,u_createTime,u_upgradeTime from t_user where u_id='".$id."'");
        if ($type){
            $time = "";
            if (strtotime($type['u_createTime']) <= strtotime($type['u_upgradeTime'])){
                $time = "display-hide";
            }
        }
        $data = array(
            'id'   => $this->options['id'],
            'time' => $time,
        );
        if ($type['u_type'] == 1){
            $this->setReplaceData($data);
            $this->setTempAndData("companyEdit");
        }else{
            $this->setReplaceData($data);
            $this->setTempAndData();
        }
        $this->show();
    }
}
