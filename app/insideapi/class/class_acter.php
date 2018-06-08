<?php

/**
 * 角色类（控制器扩展类）
 * 身份说明：0－无身份；1-用户；2-雇员
 */
//普通访客
abstract class guest extends controller {

    public function __construct($options = [], $power = []) {
        parent::__construct($options, [0, 1, 2], $power);
		
        //$this->head = F::readFile(APPROOT. '/template/cn/share/head.html');

        $this->head = array_key_exists('_ajax', $this->options) ? '' : F::readFile(APPROOT . '/template/cn/share/head.html');
        //$this->foot = array_key_exists('_ajax', $this->options) ? '' : F::readFile(APPROOT . '/template/cn/share/footer.html');

        //$this->pagehead = F::readFile($GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/front/header.html');
        //$this->pagefoot = parent::jsWidget() . F::readFile($GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/front/footer.html');
    }

}



/**
 * api
 *
 */
//
abstract class api extends controller {

    public function __construct($options = [], $power = []) {
        parent::__construct($options, [0, 1, 2], $power);
        if(attrib::getSystemParaByKey('site_state') == 0){
            $this->show(message::getJsonMsgStruct('0911'));
            exit;
        }
        $this->head = F::readFile(APPROOT. '/template/cn/share/head.html');

        //$this->pagehead = F::readFile($GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/front/header.html');
        //$this->pagefoot = parent::jsWidget() . F::readFile($GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/front/footer.html');
    }

    /**
     * 跟系统的http_build_str()功能相同，但不用安装pecl_http扩展
     * author ranqin
     * @param array     $array      需要组合的数组
     * @param string    $separator  连接符
     * @return string               连接后的字符串
     * eg: 举例说明
     */
    public function http_build_string($array, $separator='&'){
        $string = '';
        foreach($array as $key=>$val){
            $string .= "{$key}={$val}{$separator}";
        }
        //去掉最后一个连接符
        return substr($string, 0, strlen($string) - strlen($separator));
    }

    /**
     * 检测手机号码格式是否正确
     * @param string $mobile
     * @return boolean
     */
    public function isMobile($mobile) {
        return preg_match('/^0?(13[0-9]|15[012356789]|17[0678]|18[0-9]|14[57])[0-9]{8}$/', $mobile);
    }

}


// 用户控制模块
abstract class member extends controller {

    public function __construct($options = [], $power = []) {
        parent::__construct($options, [1], $power);
        if(attrib::getSystemParaByKey('site_state') == 0){
            $this->show(message::getJsonMsgStruct('0911'));
            exit;
        }		
    }

}

// 员工控制模块
abstract class worker extends controller {

    public function __construct($options = [], $power = []) {
        parent::__construct($options, [2], $power);
        if(attrib::getSystemParaByKey('site_state') == 0){
            $this->show(message::getJsonMsgStruct('0911'));
            exit;
        }		
    }

}

// 论坛控制模块
abstract class bbs extends controller {

    public function __construct($options = [], $power = []) {
        parent::__construct($options, $checkuser);
        $this->pagehead = F::readFile($GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/front/bbs/header.html');
        $this->pagefoot = parent::jsWidget() . F::readFile($GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/front/bbs/footer.html');
    }

}

//手机端有身份数据请求基类
//abstract class mobileservice extends controller {
//
//    public $flag = TRUE;
//
//    public function __construct($options = [], $power = true) {
//        parent::__construct($options, $checkuser);
//
//        //以下部分用于当手机端用户的session失效时，自动为用户登陆
//        $userID = ($this->options["userID"]) ? ($this->options["userID"]) : "null";             //一定要校验上传的userID值，否则每次都会为当前用户从新生成新的session
//        $token = ($this->options["token"]) ? ($this->options["token"]) : "null";                //服务器端为用户生成的token值，该值将被用户用户做持久登陆的session_id()值
//        session_id($token);                                                                     //改写当前sessionid值为移动端用户登陆时的token值
//        session_start();
//        if (strlen($userID) == 32 & strlen($token) == 32) {
//            if ($_SESSION["userID"] != $userID) {
//                $db = new MySql();
//                $sql = "SELECT u_nick,u_pwd,u_name,u_type,u_pwd,u_name,u_state,ac_score,u_tel,u_blevel,u_slevel,u_token,ac_djzj,ac_zhifubao FROM v_user WHERE u_id = '$userID'";
//                $db->Query($sql);
//                $row = $db->getCurRecode(PDO::FETCH_ASSOC);
//                if ($row["u_token"] == $token) {
//                    $_SESSION["token"] = $token;
//                    $_SESSION["userID"] = $userID;
//                    $_SESSION["userNick"] = $row["u_nick"];
//                    $_SESSION["zhifubao"] = $row["ac_zhifubao"];
//                    $_SESSION['userType'] = $row["u_type"];
//                    $_SESSION['userPwdMd5'] = $row["u_pwd"];
//                    $_SESSION["ac_ldzj"] = $row["ac_ldzj"];
//                    $_SESSION["ac_djzj"] = $row["ac_djzj"];
//                    $_SESSION['userBLevel'] = $row["u_blevel"];
//                    $_SESSION['userSLevel'] = $row["u_slevel"];
//                }
//            }
//        }
//        $this->checkusertype = array(1, 2, 3); //买家、卖家
//
//        $arr = json_decode($this->chkAccPower(), true);
//        if ($arr["id"] == "0004" || $arr["id"] == "1039") {
//            $this->flag = FALSE;
//        }
//    }
//
//}
