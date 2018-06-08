<?php

class head extends guest {
    function __construct($options = array(), $power = array()) {
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
//                     $username = $userInfo['u_companyName'];
                    $username = $userInfo['u_nick'];
                }
                $mem = 'https://'.MEMBERURL;
            }else{
                $username = "请 登录";
                $mem = "https://".UCURL;
            }
        }else{
            $username = "请 登录";
             $mem = "https://".UCURL;
        }
        $data = array(
            'username'=>$username,
            'mem' => $mem,
            'webUrl' => "https://oauth2.yunlianhui.com/Erp/login?code=".session_id()."&redirect=".urlencode('https://www.yunlianhui.com')
        );
        $this->show(message::getJsonMsgStruct('1001', $data)); //登录成功
    }
}