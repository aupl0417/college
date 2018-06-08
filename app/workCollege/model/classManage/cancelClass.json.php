<?php
/*=============================================================================
#     FileName: cancelClass.json.php
#         Desc: 撤销班级
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:48:12
#      History:
#      Paramer:
=============================================================================*/
class cancelClass_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();
        $res = $db->update('tang_class',['cl_state'=>-1]," cl_id='{$options['id']}'");

        if (1 != $res) {
           die($this->show(message::getJsonMsgStruct(1002,'撤销失败')));
        }
        die($this->show(message::getJsonMsgStruct(1001,'撤销成功')));
    }
}
