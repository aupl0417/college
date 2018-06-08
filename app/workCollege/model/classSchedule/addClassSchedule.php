<?php
/*=============================================================================
#     FileName: addClassSchedule.php
#         Desc: 新增排课
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:48:46
#      History:
#      Paramer:
=============================================================================*/
class addClassSchedule extends worker {
    function __construct($options) {
        parent::__construct($options, [500102]);
    }

    function run() {
        $db = new MySql();
        $yestoday = date('Y-m-d',strtotime("-1 day"));
        $sql = "SELECT cl_id,cl_name FROM tang_class WHERE cl_endTime>'$yestoday' AND cl_status IN(0,1) AND cl_state IN(0,1)";
        $classList = $db->getAll($sql);

        $sql = "SELECT truename,id FROM tang_ucenter_member WHERE identityType=1 ORDER BY CONVERT(username USING gbk)";
        $info['teacherList'] = $db->getAll($sql);
        $teacherList = $db->getAll($sql);

        $sql = "SELECT tra_id,tra_name FROM tang_trainingsite where tra_state=1";
        $classRoom = $db->getAll($sql);

        $jsData = array(
            'classList'   => array_column($classList,'cl_name','cl_id'),
            'teacherList' => array_column($teacherList,'truename','id'),
            'classRoom'   => array_column($classRoom,'tra_name','tra_id'),
        );
        $data = array(
           'code' => '500102',
           'jsData' => json_encode($jsData),
        );

        $db = new MySql();

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
