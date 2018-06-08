<?php
/*=============================================================================
#     FileName: editTeam.json.php
#         Desc: 修改班级分组
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-12 10:10:23
#      History:
#      Paramer:
=============================================================================*/
class editTeam_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500105]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'班级ID错误')));
        }

        if (!isset($options['allowableNumber']) || empty($options['allowableNumber'])) {
            die($this->show(message::getJsonMsgStruct(1002,'允许报名数不能为空')));
        }

        if (!isset($options['team']) || empty($options['team'])) {
            die($this->show(message::getJsonMsgStruct(1002,'分组数不能为空')));
        }

        //if (!isset($options['team'],$options['studentNum']) || (empty($options['team']) && empty($options['studentNum']))) {
        //    die($this->show(message::getJsonMsgStruct(1002,'分组数和每组人数不能为空')));
        //}

        $db = new MySql();
        $data = array(
            'cl_allowableNumber' => intval($options['allowableNumber']),
            'cl_teamStudentNum'  => intval($options['studentNum']),
            'cl_teamNum'         => intval($options['team']),
        );

        $res = $db->update('tang_class',$data," cl_id='{$options['id']}'");

        if (1 != $res) {
            die($this->show(message::getJsonMsgStruct(1002,'添加分组信息失败')));
        }

        die($this->show(message::getJsonMsgStruct(1001,'添加分组信息成功')));
    }
}
