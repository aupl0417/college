<?php
/*=============================================================================
#     FileName: delCourseTemp.json.php
#         Desc: 删除课程模板
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-11 09:54:43
#      History:
#      Paramer:
=============================================================================*/
class delCourseTemp_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500104]);
    }

    function run() {
        $options = $this->options;
        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();
        $res = $db->delete('tang_class_table_templet',"ctt_id='{$options['id']}'");
        if (1 != $res) {
           die($this->show(message::getJsonMsgStruct(1002,'删除失败')));
        }
        die($this->show(message::getJsonMsgStruct(1001,'删除成功')));
    }
}
