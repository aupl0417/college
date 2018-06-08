<?php

//短信模块类。
class sms_json extends page {

    function run() {
        $phone = isset($this->options['phone']) ? $this->options['phone'] : '';
        $validatemode = isset($this->options['validatemode']) ? intval($this->options['validatemode']) : ''; //1：验证图形验证码,0：不需要验证
        //如果图形验证码就校验验证码，否则就不需要校验
        if ($validatemode == 1) {
            $vcode = isset($this->options['vcode']) ? $this->options['vcode'] : '';
            //判断图形验证码是否一致
            $validate = new validate();
            if (!$validate->getValidate(strtolower($vcode))) {
                return message::getJsonMsgStruct('0022');
            }
        }
        //判断电话合法
        if (!F::isPhone($phone)) {
            return message::getJsonMsgStruct('0048'); //电话不合法
        }
        //防止频繁发送，间隔需要120秒
        session_start();
        if (!isset($_SESSION['posttime'])) {
            $_SESSION['posttime'] = time();
        } elseif (time() - $_SESSION['posttime'] < SMS_SENDINTERVAL) {
            return message::getJsonMsgStruct('0054'); //发送太频繁
        } else {
            $_SESSION['posttime'] = time();
        }
        //得到授权
        $token = md5(F::getMtID());
        //判断是否手机重复
        if (!isset($_SESSION['userID'])) {
            $db = new MySql();
            $sql = "SELECT COUNT(*) FROM c_user WHERE u_tel = '$phone' AND u_type IN(1,2)";
            $db->Query($sql);
            if ($db->getResultCol() != 0) {
                unset($_SESSION['posttime']);
                return message::getJsonMsgStruct('0052'); //有重复
            }
        }
        //发送验证码
        $sms = new sms();
        $ret = $sms->SendValidateSMS($phone, '优品试用', $token);
        switch ($ret) {
            case 1://success
                return message::getJsonMsgStruct('0049', $token); //成功
            case 0://fail
                $_SESSION['posttime'] = NULL;
                return message::getJsonMsgStruct('0050'); //失败
            case -1://no access
                return message::getJsonMsgStruct('0055'); //发送太频繁
            default :
                return message::getJsonMsgStruct('0055'); //默认没有授权
        }
    }

}
