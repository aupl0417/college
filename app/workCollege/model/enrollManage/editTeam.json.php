<?php
/*=============================================================================
#     FileName: editTeam.json.php
#         Desc: 修改学员分组
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-08 20:07:12
#      History:
#      Paramer:
=============================================================================*/
class editTeam_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50010303]);
    }

    function run() {
        $options = $this->options;

        $needParams = array(
            'id'   => '报名ID',
            'team' => '分组信息',
        );

        foreach ($needParams as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,$v.'参数错误')));
            }
        }

        $db = new MySql();
        $res = $db->update('tang_student_enroll',array('tse_team'=>intval($options['team']))," tse_id='{$options['id']}'");

        if (1 != $res) {
            die($this->show(message::getJsonMsgStruct(1002,'分组失败')));
        }

        die($this->show(message::getJsonMsgStruct(1001,'分组成功')));
    }
}
