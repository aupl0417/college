<?php

//登录的模块类。
class checkmail_json extends guest {

    function run() {
        $uid = isset($this->options['emailcode']) ? trim($this->options['emailcode']) : '';
        $email = isset($this->options['email']) ? trim($this->options['email']) : '';
        $crypt = new STD3Des('yikuaiyoukey');
        $uid = $crypt->decrypt(base64_decode($uid));
        $email = base64_decode($email);
        $mem = new cache();
        $cache_uid = $mem->get('emailcode_' . $uid);
        if (md5($uid) != $cache_uid) {
            $msg = message::getMessageByID(10005);
            $gourl = '/?model=user';
            return message::ShowEx($msg, $gourl, 3000);
        }

        //修改用户邮箱认证状态
        $db = new MySql();
        //获取用户已经认证的记录
        $sql = "SELECT u_auth FROM c_user WHERE u_id = '$uid'";
        $db->Query($sql);
        $res = $db->getResultCol();
        $auth_res = $res . '3,'; //在用户认证值后面加 （3，）表示完成邮箱认证
        $sql = "UPDATE c_user set u_email = '$email', u_auth = '$auth_res' WHERE u_id = '$uid'";

        if (($db->Execute($sql)) == 1) {
            $mem->del('emailcode_' . $uid);
            $msg = message::getMessageByID(10004);
            $gourl = '/?model=user';
            return message::ShowEx($msg, $gourl, '3000');
        } else {
            $mem->del('emailcode_' . $uid);
            $msg = message::getMessageByID(10003);
            $gourl = '/?model=user';
            return message::ShowEx($msg, $gourl, '3000');
        }
    }

}
