<?php

class add extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }
    function run() {
        $db = new MySql();
        if(isset($_SESSION['userID'])){
            //检测发送给雇员还是会员（以dttx开头的昵称就是发送给雇员）
            if(substr($this->options['nick'],0,4) == 'dttx'){    //发给雇员
                $userInfo = $db->getField("select e_id from t_employee where e_id = '".$this->options['nick']."'");
                $userInfo = '4-'.$userInfo;
            }else{
                $userInfo = $db->getField("select u_id from t_user where u_nick = '".$this->options['nick']."'");
                $userInfo = '3-'.$userInfo;
            }

            if(isset($userInfo) && $userInfo != '' && $userInfo != null){
                $this->show(message::getJsonMsgStruct('1001', $userInfo)); //登录成功
            }else{
                $this->show(message::getJsonMsgStruct('1002', "失败")); //登录失败
            }
        }else{
            $this->show(message::getJsonMsgStruct('1002', "失败")); //登录失败
        }
    }
}