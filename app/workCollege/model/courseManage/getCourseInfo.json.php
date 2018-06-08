<?php
/*=============================================================================
#     FileName: getCourseInfo.json.php
#         Desc: 获取课程信息
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:46:23
#      History:
=============================================================================*/

class getCourseInfo_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [50010402]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();
        $info = $db->getRow("SELECT * FROM tang_course WHERE co_id='{$options['id']}'");

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct(1002,'修改失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,$info));
    }
}
