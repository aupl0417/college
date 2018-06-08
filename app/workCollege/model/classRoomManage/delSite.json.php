<?php
/*=============================================================================
#     FileName: delSite.json.php
#         Desc: 删除课课室信息
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-29 16:58:46
#      History:
#      Paramer:
=============================================================================*/
class delSite_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500401]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();

        $delRes = $db->update('tang_trainingsite',array('tra_state'=>-1),"tra_id='{$options['id']}'");

        if (1 > $delRes) {
            die($this->show(message::getJsonMsgStruct(1002,'删除课室信息失败')));

        }
        die($this->show(message::getJsonMsgStruct(1001,'删除课室信息成功')));
    }
}
