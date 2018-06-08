<?php

//登录的模块类。
class checkphone_json extends guest {

    function run() {
        $phone = isset($this->options['phone']) ? $this->options['phone'] : '';
        if (F::isPhone($phone)) {
            return message::getJsonMsgStruct('0048');
        }
        $db = new MySql();
        $sql = "SELECT count(*) FROM c_users where u_tel = '$phone'";
        $db->Query($sql);
        if ($db->getResultCol() == 0) {
            return message::getJsonMsgStruct('0053');
        } else {
            return message::getJsonMsgStruct('0052');
        }
    }

}
