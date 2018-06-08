<?php

class login extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        $user = new user();
        if(isset($_SESSION['userID'])){
            $userInfo = $user->getUserByID($_SESSION['userID']);
            if(isset($userInfo) && $userInfo != '' && $userInfo != null){
                $type = isset($userInfo['u_type']) ? $userInfo['u_type'] : "";
                if($type == 0){
                    $username = $userInfo['u_nick'];
                }else{
                    $username = $userInfo['u_nick'];
                }
                $logout = '退出';
                $register = "http://".INSIDEAPI."/index/logout";
            }else{
                $username = "请 登录";
                $logout = " ";
                $register = " ";
            }
        }else{
            $username = "请 登录";
            $logout = " ";
            $register = " ";
        }
        $data = array(
            'username'=>$username,
            'logout' => $logout,
            'register' => $register,
            'mem' => "http://".INSIDEAPI."/work/interfaceList",
            'webUrl' => 'https://'.WWWURL,
        );
        $this->show(message::getJsonMsgStruct('1001', $data)); //登录成功
    }

}
