<?php
/*=============================================================================
#     FileName: editCourseDirection.json.php
#         Desc: 修改课程分类
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 21:38:29
#      History:
=============================================================================*/

class editCourseDirection_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [50010403]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;

        $needParamer = array('id','name','description');
        foreach ($needParamer as $v) {
            if (!isset($options[$v]) || empty($options[$v])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误，请检查后重试")));
            }
        }

        if ($this->db->count('tang_study_direction',"sd_name='".$options['name']."' AND sd_id<>'{$options['id']}'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的课程分类，请检查再试')));
        }

        $data = array(
            'sd_name'             => trim($options['name']),
            'sd_description'      => $options['description'],
            'sd_state'            => intval( $options['state']),
        );


        if(1 != $this->db->update('tang_study_direction',$data," sd_id='{$options['id']}'")){
            die($this->show(message::getJsonMsgStruct(1002,'修改失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'修改成功'));
    }
}
