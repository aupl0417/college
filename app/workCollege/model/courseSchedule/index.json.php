<?php
/*=============================================================================
#     FileName: index.json.php
#         Desc: 排课表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:45:24
#      History:
#      Paramer:
=============================================================================*/
class index_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50010101]);
    }

    function run() {
        $options = $this->options;

        $startTime = isset($options['startTime']) ? date('Y-m-d H:i:s',$options['startTime']) : date('Y-m-01 00:00:00');
        $endTime   = isset($options['endTime']) ? date('Y-m-d H:i:s',$options['endTime']) : date('Y-m-d 23:59:59');

        $sql = "SELECT cta_startTime startTime,cta_endTime endTime,co_name title FROM tang_class_table cta
            LEFT JOIN tang_course co ON cta.cta_courseId=co.co_id WHERE cta_startTime BETWEEN '{$startTime}' AND '{$endTime}'";

        $db = new MySql();
        $list = $db->getAll($sql);

        if (empty($list)) {
            die($this->show(message::getJsonMsgStruct(1002,array())));
        }

        $this->show(message::getJsonMsgStruct(1001,$list));
    }
}
