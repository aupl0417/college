<?php
/*=============================================================================
#     FileName: getCourseTemplateInfo.json.php
#         Desc: 获取课程模板信息
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:46:31
#      History:
=============================================================================*/

class getCourseTemplateInfo_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [50010402]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();
        $info = $db->getRow("SELECT ctt_id,ctt_name,ctt_describe TempDescribe,ctt_course FROM tang_class_table_templet WHERE ctt_id='{$options['id']}'");

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct(1002,'获取模板信息失败')));
        }

        $course = $db->getAll("SELECT co_id,co_name,co_hour,co_credit FROM tang_course WHERE co_id IN({$info['ctt_course']})");

        $courseList = $db->getAll("SELECT co_id,co_name FROM tang_course WHERE co_state=1");
        $courseList = array_column($courseList,'co_name','co_id');

        if (!empty($course)) {
            foreach ($course as $v) {
                $info['course'][] = array(
                    'course' => $courseList,
                    'co_id'  => $v['co_id'],
                    'hour'   => $v['co_hour'].' 课时',
                    'credit' => $v['co_credit'].' 学分',
                );
            }
        }

        $this->show(message::getJsonMsgStruct(1001,$info));
    }
}
