<?php
/*=============================================================================
#     FileName: editCourse.json.php
#         Desc: 修改课程
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:46:06
#      History:
=============================================================================*/

class editCourse_json extends worker {
    private $db;
    function __construct($options) {
        parent::__construct($options, [50010401]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;

        $needParamer = array('name','studyDirectionId','description','content','state','coID','hour','credit','gradeID');
        foreach ($needParamer as $v) {
            if (!isset($options[$v]) || empty($options[$v])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误，请检查后重试")));
            }
        }

        if ($this->db->count('tang_course',"co_name='".$options['name']."' AND co_id<>'{$options['coID']}'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的课程，无需重复添加')));
        }

        $data = array(
            'co_name'             => trim($options['name']),
            'co_studyDirectionId' => trim($options['studyDirectionId']),
            'co_description'      => $options['description'],
            'co_content'          => $options['content'],
            'co_state'            => $options['state'],
            'co_hour'             => $options['hour'],
            'co_credit'           => $options['credit'],
            'co_gradeID'          => $options['gradeID'],
            'co_logo'             => $options['courseLogo'],
            'co_updateTime'       => F::mytime(),
        );

        if(1 != $this->db->update('tang_course',$data," co_id='{$options['coID']}'")){
            die($this->show(message::getJsonMsgStruct(1002,'修改失败')));
        }

        $this->show(message::getJsonMsgStruct(1001,'修改成功'));
    }
}
