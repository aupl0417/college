<?php
/**
 * 修改 特批标识
 * 提现可不用遵守银行卡户名是真实姓名/公司名/法人/经营者
 * 0：无特批；1：特批
*/
class editSpecialAllow extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60129]);
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $specialAllow = $db->getField("select u_specialAllow from t_user where u_id='".$id."'");
        if ($specialAllow){
            $allow_op = "<option value='1' selected>允许</option><option value='0'>不允许</option>";
        }else{
            $allow_op = "<option value='1'>允许</option><option value='0' selected>不允许</option>";
        }
        $data = array(
            'id'		    => $id,
            'allow_op'		=> $allow_op,
            'specialAllow'	=> $specialAllow,
        );
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
