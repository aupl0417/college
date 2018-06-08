<?php

class schedule_json extends member {

    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        (!$_SESSION && !$_SESSION['userID']) && die($this->show(message::getJsonMsgStruct('1002', '请登录')));
        $options = $this->options;

        $startTime = isset($options['startTime']) ? date('Y-m-d H:i:s',$options['startTime']) : date('Y-m-01 00:00:00');
        $endTime   = isset($options['endTime']) ? date('Y-m-d H:i:s',$options['endTime']) : date('Y-m-d 23:59:59');
        $userId = $this->db->getField('select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '" and identityType=1');
        !$userId && die($this->show(message::getJsonMsgStruct('1002', '讲师不存在')));

        $sql = "SELECT cta_startTime startTime,cta_endTime endTime,co_name title FROM tang_class_table cta
            LEFT JOIN tang_course co ON cta.cta_courseId=co.co_id WHERE cta_startTime BETWEEN '{$startTime}' AND '{$endTime}' and cta_teacherId='" . $userId . "'";

        $db = new MySql();
        $list = $db->getAll($sql);

        if (empty($list)) {
            die($this->show(message::getJsonMsgStruct(1002, array())));
        }

        $this->show(message::getJsonMsgStruct(1001, $list));
    }

}
