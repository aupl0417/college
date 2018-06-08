<?php

class delEnroll_json extends guest {

    function run() {
        (!isset($this->options['classId']) || empty($this->options['classId'])) && die($this->show(message::getJsonMsgStruct('1002', '班级ID非法')));
        !$_SESSION['userID'] && die($this->show(message::getJsonMsgStruct('1002', '请先登录')));

        $classId = $this->options['classId'] + 0;
        $userId  = $_SESSION['userID'];

        $db = new MySql();
        $uid = $db->getField('select id from tang_ucenter_member where userId="' . $userId . '"');
        if(!$uid){
            die($this->show(message::getJsonMsgStruct('1002', '用户不存在')));
        }

        $params = array(
            'userId'  => $uid,
            'classId' => $classId,
        );

        $result = apis::request('/college/api/deleteEnroll.json', $params, true);
        if($result['code'] != '1001'){
            die($this->show(message::getJsonMsgStruct($result['code'], $result['data'])));
        }

        $this->show(message::getJsonMsgStruct('1001', '取消报名成功'));
    }
}
