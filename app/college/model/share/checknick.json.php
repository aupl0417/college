<?php

//登录的模块类。
class checknick_json extends guest {

    function run() {
        $us = new users();
        $arr = $us->getUserByNick($this->options['nick']);
        $arrnum = count($arr);

        if (( $arrnum != 0 ) && ( $this->options['nick'] != '' )) {
            return message::getJsonMsgStruct('1006'); //返回‘该用户已存在’
        }

        $nick = preg_match("/[^a-zA-Z0-9]/", $this->options['nick'], $match);
        if ($nick || ( $this->options['nick'] == '' )) {
            return message::getJsonMsgStruct('1007'); //返回‘用户名非法’
        }

        return message::getJsonMsgStruct('1008'); //返回‘用户名可以使用’
    }

}
