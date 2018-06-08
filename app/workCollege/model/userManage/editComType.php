<?php

class editComType extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60123]);			
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $companyTypes = F::getAttrs(4);
//         $companyTypes = F::array2Options($companyTypes);
        $db = new MySql();
        $type = $db->getField("select u_companyType from t_user where u_id='".$id."'");
        if ($type){
            $companyTypes = F::array2Options($companyTypes,[$type]);
        }else{
            $companyTypes = "<option value='' selected>请选择企业类型</option>".F::array2Options($companyTypes);
        }
//         dump($type);die;
        $data = array(
            'id'   => $id,
            'companyTypes'=> $companyTypes,
        );
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
