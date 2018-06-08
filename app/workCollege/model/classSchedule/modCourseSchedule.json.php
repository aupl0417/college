<?php
/*=============================================================================
#     FileName: modCourseSchedule.json.php
#         Desc: 临时调课
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:49:14
#      History:
=============================================================================*/

class modCourseSchedule_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [500102]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;

        $needParamer = array('id','trainingsiteId','teacherId','startTime','endTime');

        foreach ($needParamer as $v) {
            if (!isset($options[$v]) || empty($options[$v])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误，请检查后重试")));
            }
        }

        if (strtotime($options['endTime']) < strtotime($options['startTime'])) {
            list($options['startTime'],$options['endTime']) = array($options['endTime'],$options['startTime']);
        }

        $data = array(
            'cta_trainingsiteId' => $options['trainingsiteId'],
            'cta_teacherId'      => $options['teacherId'],
            'cta_startTime'      => $options['startTime'],
            'cta_endTime'        => $options['endTime'],
        );

        if(1 != $this->db->update('tang_class_table',$data," cta_id='{$options['id']}'")){
            die($this->show(message::getJsonMsgStruct(1002,'调课失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'调课成功'));
    }
}
