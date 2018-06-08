<?php
/*=============================================================================
#     FileName: addCourse.json.php
#         Desc: 添加课程
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:45:31
#      History:
=============================================================================*/
/**
 * @api                    {post}/courseManage/addCourse.json 添加课程
 * @apiDescription         添加课程
 * @apiName                addCourse_json
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
class addCourse_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [50010401]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;

        $needParamer = array('name','studyDirectionId','description','content','hour','credit','gradeID');
        foreach ($needParamer as $v) {
            if (!isset($options[$v]) || empty($options[$v])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误，请检查后重试")));
            }
        }

        if ($this->db->count('tang_course',"co_name='".$options['name']."'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的课程，无需重复添加')));
        }

        $data = array(
            'co_name'             => trim($options['name']),
            'co_studyDirectionId' => trim($options['studyDirectionId']),
            'co_description'      => $options['description'],
            'co_content'          => $options['content'],
            'co_hour'             => $options['hour'],
            'co_credit'           => $options['credit'],
            'co_logo'             => $options['courseLogo'],
            'co_gradeID'          => $options['gradeID'],
            'co_state'            => 1,
            'co_createTime'       => F::mytime(),
        );

        if(1 != $this->db->insert('tang_course',$data)){
            die($this->show(message::getJsonMsgStruct(1002,'添加失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'添加成功'));
    }
}
