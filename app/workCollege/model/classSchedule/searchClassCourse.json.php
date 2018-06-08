<?php
/*=============================================================================
#     FileName: searchClassCourse.json.php
#         Desc: 新增排课
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:49:29
#      History:
#      Paramer:
=============================================================================*/
class searchClassCourse_json extends worker {
    function run() {
        $options = $this->options;

        if (!isset($options['classID']) || empty($options['classID'])) {
            die($this->show(message::getJsonMsgStruct('1002','班级ID错误')));
        }

        $db = new MySql();
        $classInfo = $db->getRow("SELECT cl_startTime startDate,cl_endTime endDate FROM tang_class WHERE cl_id='{$options['classID']}'");

        if (empty($classInfo)) {
            die($this->show(message::getJsonMsgStruct('1002','没有找到班级信息')));
        }

        if (strtotime($classInfo['startDate']) < strtotime('now')) {
            $classInfo['startDate'] = date('Y-m-d');
        }

        $sql = "SELECT co_id,co_name FROM tang_class_course cc LEFT JOIN tang_course co ON cc.cc_courseId=co.co_id WHERE cc_classId='{$options['classID']}'";
        $courseList = $db->getAll($sql);

        if (empty($courseList)) {
            die($this->show(message::getJsonMsgStruct('1002','没有找到课程')));
        }

        $info = array(
            'courseList' => F::array2Options(array_column($courseList,'co_name','co_id')),
            'classInfo'  => $classInfo,
        );

        die($this->show(message::getJsonMsgStruct('1001',$info)));
    }
}
