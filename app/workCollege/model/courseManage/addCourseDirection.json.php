<?php
/*=============================================================================
#     FileName: addCourseDirection.json.php
#         Desc: 添加课程分类
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 21:02:35
#      History:
=============================================================================*/
class addCourseDirection_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [50010403]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;

        $needParamer = array('name','description');
        foreach ($needParamer as $v) {
            if (!isset($options[$v]) || empty($options[$v])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误，请检查后重试")));
            }
        }

        if ($this->db->count('tang_study_direction',"sd_name='".$options['name']."'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的课程分类，无需重复添加')));
        }

        $data = array(
            'sd_name'             => trim($options['name']),
            'sd_description'      => $options['description'],
            'sd_createTime'       => F::mytime(),
        );

        if(1 != $this->db->insert('tang_study_direction',$data)){
            die($this->show(message::getJsonMsgStruct(1002,'添加失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'添加成功'));
    }
}
