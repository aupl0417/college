<?php
/*=============================================================================
#     FileName: addStudent.json.php
#         Desc: 添加学员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:48:26
#      History:
#      Paramer:
=============================================================================*/
class addStudent_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;
        if (!isset($options['schValue']) || empty($options['schValue'])) {
            die($this->show(message::getJsonMsgStruct(1002,'用户名或手机号码错误')));
        }

        if (!isset($options['clID']) || empty($options['clID'])) {
            die($this->show(message::getJsonMsgStruct(1002,'班级ID错误')));
        }

        $options['schValue'] = trim($options['schValue']);

        $db = new MySql();
        $studentID = $db->getField("SELECT id FROM tang_ucenter_member WHERE username='{$options['schValue']}' || mobile='{$options['schValue']}'");

        if (empty($studentID)) {
            die($this->show(message::getJsonMsgStruct(1002,'获取学员信息失败')));
        }

        //是否报名
        $isEnroll = $db->count('tang_student_enroll',"tse_userId='{$studentID}' AND tse_classId='{$options['clID']}'");
        if (empty($isEnroll)) {
            die($this->show(message::getJsonMsgStruct(1002,'该学员没有报名')));
        }

        $allowNum = $db->getField("SELECT cl_allowableNumber FROM tang_class WHERE cl_id='{$options['clID']}'");
        $studentNum = $db->count('tang_class_student',"cs_classId='{$options['clID']}'");

        if ($allowNum <= $studentNum) {
            die($this->show(message::getJsonMsgStruct(1002,'班级人数已满，不可添加')));
        }

        $exist = $db->count('tang_class_student',"cs_studentId={$studentID} AND cs_classId='{$options['clID']}'");

        if ($exist) {
            die($this->show(message::getJsonMsgStruct(1002,'该学员已经报名此班，无需重复添加')));
        }

        $insertData = array(
            'cs_classId'    => $options['clID'],
            'cs_studentId'  => $studentID,
            'cs_createTime' => F::mytime(),
        );
        $res = $db->insert('tang_class_student',$insertData);

        if (1 != $res) {
            die($this->show(message::getJsonMsgStruct(1002,'添加失败')));
        }

        die($this->show(message::getJsonMsgStruct(1001,'添加成功')));
    }
}
