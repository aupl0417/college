<?php

class dealEnroll_json extends guest {

    function run() {
        (!isset($this->options['classId']) || empty($this->options['classId'])) && die($this->show(message::getJsonMsgStruct('1002', '班级ID非法')));
        (!isset($this->options['province']) || empty($this->options['province'])) && die($this->show(message::getJsonMsgStruct('1002', '请选择所属区域')));
        !$_SESSION['userID'] && die($this->show(message::getJsonMsgStruct('1002', '请先登录')));

        $classId = $this->options['classId'] + 0;
        $province = $this->options['province'];
        $userId  = $_SESSION['userID'];
        $carService = isset($this->options['carService']) && !empty($this->options['carService']) ? $this->options['carService'] + 0 : 0;
        if($carService){
            (!isset($this->options['arrivalTime']) || empty($this->options['arrivalTime'])) && die($this->show(message::getJsonMsgStruct('1002', '接站时间不能为空')));
            (!isset($this->options['station'])     || empty($this->options['station'])) && die($this->show(message::getJsonMsgStruct('1002', '请选择接站地点')));
            (!isset($this->options['counts'])      || empty($this->options['counts'])) && die($this->show(message::getJsonMsgStruct('1002', '请选择接站人数')));
        }

        $db = new MySql();
        $userInfo = $db->getRow('select username,avatar from tang_ucenter_member where userId="' . $userId . '"');
        if(!$userInfo){
            die($this->show(message::getJsonMsgStruct('1002', '用户不存在')));
        }

        $params = array(
            'userId'  => $userId,
            'classId' => $classId,
            'province' => $province,
            'arrivalTime' => $this->options['arrivalTime'] ? $this->options['arrivalTime'] : '',
            'station' => isset($this->options['station']) ? $this->options['station'] : '',
            'counts' =>  isset($this->options['counts']) ? $this->options['counts'] : '',
            'payType' => 0,
        );

        $result = apis::request('/college/api/enroll.json', $params, true);
        if($result['code'] != '1001'){
            die($this->show(message::getJsonMsgStruct($result['code'], $result['data'])));
        }

        $this->show(message::getJsonMsgStruct('1001', '报名成功'));
    }
}
