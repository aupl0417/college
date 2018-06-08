<?php
/*=============================================================================
#     FileName: delStudent.json.php
#         Desc: 删除学员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:47:45
#      History:
#      Paramer:
=============================================================================*/
class delStudent_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id'],$options['clID']) || empty($options['id']) || empty($options['clID'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();
        $res = $db->delete('tang_class_student',"cs_id='{$options['id']}'");
        if (1 != $res) {
           die($this->show(message::getJsonMsgStruct(1002,'删除失败')));
        }
        die($this->show(message::getJsonMsgStruct(1001,'删除成功')));
    }
}
