<?php

//登录的模块类。
class checklogin_json extends guest {

    function run() {
        $t = new validate();
        //校验验证码
        if (!$t->getValidate($this->options['validate'])) {
            return message::getJsonMsgStruct('0022');
        }
        $u = new users();
        $res = $u->checkUser($this->options['nick'], $this->options['pwd']);
        switch ($res) {
            case '0001':
                $usertype = $u->getUserAttrib('userType');
                $message = sprintf('%s于%s成功登录系统', $u->getUserAttrib('userNick'), date('Y-m-d H:i:s', time()));
                $this->log($message, 'info');
                return json_encode(array(
                    'id' => '0001',
                    'msg' => message::getMessageByID('0001'),
                    'info' => $usertype,
                ));
            default :
                return message::getJsonMsgStruct($res); //返回‘登录系统失败，检查账号密码是否正确，请重试。’
        }
    }

}
