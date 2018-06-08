<?php

/**
 * 角色类（控制器扩展类）
 * 身份说明：0－无身份；1-会员；2-雇员
 */
//普通访客
abstract class guest extends controller {

    public function __construct($options = [], $power = []) {
        parent::__construct($options, [0, 1, 2], $power);

        $isLogin = 0;
        $userNick = ''; 
        if($_SESSION['userID'] && in_array($_SESSION['userType'], array(0,1))){
            $isLogin = 1;
            $userNick = $_SESSION['userNick'];
        }else {
            $_SESSION['callback'] = 'http://college' . DOMAIN . $_SERVER['REQUEST_URI'];
//            header("Location:https://u" . DOMAIN . '/login');
        }

        $this->setReplaceData(['isLogin'=>$isLogin, 'userNick'=>$userNick, 'domain' => DOMAIN]);
        $this->head = F::readFile(APPROOT. '/template/cn/share/head.html');
        $this->foot = F::readFile(APPROOT. '/template/cn/share/foot.html');
    }

}


