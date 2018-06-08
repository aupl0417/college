<?php
/*=============================================================================
#     FileName: editCourseTemplate.json.php
#         Desc: 修改模板
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:46:16
#      History:
=============================================================================*/

class editCourseTemplate_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [50010402]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;

        $needParamer = array(
            'state'     => '状态',
            'id'        => '模板ID',
            'name'      => '模板名',
            'condition' => '学习前置条件',
            'gradeID'   => '课程级别',
            'describe'  => '模板描述',
            'logo'      => '班级logo',
            'course'    => '课程'
        );
        foreach ($needParamer as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v} 参数错误，请检查后重试")));
            }
        }

        if ($this->db->count('tang_class_table_templet',"ctt_name='".$options['name']."' AND ctt_id<>'{$options['id']}'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的模板，无需重复添加')));
        }

        if (!isset($options['course'])) {
            die($this->show(message::getJsonMsgStruct(1002,'请添加课程排表')));
        }

        $course = array_unique(array_filter($options['course']));
        if (empty($course)) {
            die($this->show(message::getJsonMsgStruct(1002,'课程排表信息错误，请检查后重试')));
        }

        $updData = array(
            'ctt_name'       => trim($options['name']),
            'ctt_state'      => $options['state'],
            'ctt_updateTime' => F::mytime(),
            'ctt_gradeID'    => $options['gradeID'],
            'ctt_describe'   => $options['describe'],
            'ctt_logo'       => $options['logo'],
            'ctt_course'     => join(',',$course),
            'ctt_condition'  => join(',',$options['condition']),
        );

        if(1 != $this->db->update('tang_class_table_templet',$updData," ctt_id='{$options['id']}'")){
            die($this->show(message::getJsonMsgStruct(1002,'修改失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'修改成功'));
    }
}
