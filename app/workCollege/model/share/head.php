<?php

class head extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }
    function run() {
        $db = new MySql();
        if(isset($_SESSION['userID'])){
            $userInfo = $db->getRow("select * from tang_employee where e_id = '".$_SESSION['userID']."'");
            if(isset($userInfo) && $userInfo != '' && $userInfo != null){
                $username = $userInfo['e_name'];
                $work = 'https://'.WORKERURL;
            }else{
                $username = "请 登录";
                $work = "https://".WORKERURL;
            }
        }else{
            $username = "请 登录";
            $work = "https://".UCURL;
        }
        $data = array(
            'username'=>$username,
            'work' => $work,
        );
        $this->show(message::getJsonMsgStruct('1001', $data)); //登录成功
    }
}
