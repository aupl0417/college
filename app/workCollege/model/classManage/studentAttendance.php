<?php
/*=============================================================================
#     FileName: studentAttendance.php
#         Desc: 学员列表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:46:56
#      History:
#      Paramer:
=============================================================================*/
class studentAttendance extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['clID']) || empty($options['clID'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $db = new MySql();
        $sql = "SELECT co.co_id,co.co_name FROM tang_class_course cc LEFT JOIN tang_course co ON cc.cc_courseId=co.co_id WHERE cc.cc_classId='{$options['clID']}'";
        $course = $db->getAll($sql);

        $course = array_column($course,'co_name','co_id');

        $data = array(
            'code'       => '500103',
            'clID'       => $options['clID'],
            'courseList' => F::array2Options($course),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
