<?php
/*=============================================================================
#     FileName: addCourseDirection.php
#         Desc: 课程分类（学习方向）
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 20:49:31
#      History:
#      Paramer:
=============================================================================*/
class addCourseDirection extends worker {
    function __construct($options) {
        $db = new MySql();
        parent::__construct($options, [50010403]);
    }

    function run() {
        $data = array(
            'code' => '50010403',
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
