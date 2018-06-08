<?php

class getInfoByMobile_json extends worker {

    private $db;
    function __construct($options) {
        parent::__construct($options, []);
        $this->db = new MySql();
    }

    function run() {

        $options = $this->options;
        $mobile = $options['mobile'] ? $options['mobile'] : "";
        if($mobile == ""){
            return $this->show(message::getJsonMsgStruct('1002',''));
            exit;
        }else {
            //检查
            $sql = "SELECT u_nick,u_certNum FROM t_user WHERE u_tel ='".$mobile."'";
            $rs = $this->db->getRow($sql);
            if($rs){
                $nick = $rs['u_nick'];
                $cartNum = $cartNum = '**** **** **** '.substr($rs['u_certNum'], 13);
                return $this->show(message::getJsonMsgStruct('1001',"会员账号：<span class='text-danger'>".$nick."</span> 身份证信息：<span class='text-danger'>".$cartNum."</span>"));
                exit;
            }else{
                return $this->show(message::getJsonMsgStruct('1002',''));
                exit;
            }
        }
    }

}
