<?php
/*=============================================================================
#     FileName: addCourseTemplate.json.php
#         Desc: 添加课程模板
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:45:41
#      History:
=============================================================================*/
/**
 * @api                    {post}/courseManage/addCourseTemplate.json 添加课程模板
 * @apiDescription         添加课程模板
 * @apiName                addCourseTemplate_json
 * @apiGroup               courseManage 
 *
 * @apiParam {string}   courseName    课程名     必须
 *
 * @apiSuccessExample      Success-Response:
 *{
 * "id": "1001",
 * }
 *
 * @apiErrorExample        Error-Response:
 *{
 * "id": "1002",
 * }
 *
 */
class addCourseTemplate_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [500104]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;
        $needParamer = array(
            'name'      => '模板名',
            'condition' => '学习前置条件',
            'gradeID'   => '课程级别',
            'describe'  => '模板描述',
            'logo'      => '班级logo',
            'course'    => '课程'
        );

        foreach ($needParamer as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误，请检查后重试")));
            }
        }

        if ($this->db->count('tang_class_table_templet',"ctt_name='".$options['name']."'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的课程模板，无需重复添加')));
        }

        if (!isset($options['course'])) {
            die($this->show(message::getJsonMsgStruct(1002,'请添加课程排表')));
        }

        $course = array_unique(array_filter($options['course']));
        if (empty($course)) {
            die($this->show(message::getJsonMsgStruct(1002,'课程排表信息错误，请检查后重试')));
        }

        $data = array(
            'ctt_name'       => trim($options['name']),
            'ctt_gradeID'    => $options['gradeID'],
            'ctt_describe'   => $options['describe'],
            'ctt_logo'       => $options['logo'],
            'ctt_course'     => join(',',$course),
            'ctt_condition'  => join(',',$options['condition']),
            'ctt_state'      => 1,
            'ctt_createTime' => F::mytime(),
        );

        if(1 != $this->db->insert('tang_class_table_templet',$data)){
            die($this->show(message::getJsonMsgStruct(1002,'添加失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'添加成功'));
    }
}
