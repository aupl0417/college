<?php
/*=============================================================================
#     FileName: delSchedule.json.php
#         Desc: 删除课程排课信息
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:49:05
#      History:
#      Paramer:
=============================================================================*/
class delSchedule_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500102]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();

        $delRes = $db->delete('tang_class_table'," cta_id='{$options['id']}'");

        if (1 > $delRes) {
            die($this->show(message::getJsonMsgStruct(1002,'删除排课记录失败')));

        }
        die($this->show(message::getJsonMsgStruct(1001,'删除成功')));
    }
}
