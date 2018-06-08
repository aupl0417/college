<?php
/*=============================================================================
#     FileName: beginClass.json.php
#         Desc: 确认开课
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:48:15
#      History:
#      Paramer:
=============================================================================*/
class beginClass_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();
        $res = $db->update('tang_class',['cl_state'=>1]," cl_id='{$options['id']}'");

        if (1 != $res) {
           die($this->show(message::getJsonMsgStruct(1002,'确认开课失败')));
        }
        die($this->show(message::getJsonMsgStruct(1001,'确认开课成功')));
    }
}
