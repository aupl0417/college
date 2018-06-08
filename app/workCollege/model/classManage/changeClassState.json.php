<?php
/*=============================================================================
#     FileName: changeClassState.json.php
#         Desc: 修改班级状态
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-01 10:07:53
#      History:
#      Paramer:
=============================================================================*/
class changeClassState_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        if (!isset($options['state']) || empty($options['state'])) {
            die($this->show(message::getJsonMsgStruct(1002,'状态错误')));
        }

        $db = new MySql();
        $res = $db->update('tang_class',['cl_state'=>intval($options['state'])]," cl_id='{$options['id']}'");

        if (1 != $res) {
            die($this->show(message::getJsonMsgStruct(1002,'撤销失败')));
        }
        die($this->show(message::getJsonMsgStruct(1001,'撤销成功')));
    }
}
