<?php
/*=============================================================================
#     FileName: addCourse.php
#         Desc: 添加课程
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:45:36
#      History:
#      Paramer:
=============================================================================*/

class addCourse extends worker {
    function __construct($options) {
        parent::__construct($options, [500104]);
    }

    function run() {
        $db             = new MySql();
        $studyDirection = $db->getAll('SELECT sd_id,sd_name FROM tang_study_direction WHERE sd_state=0');
        $studyDirection = array_column($studyDirection,'sd_name','sd_id');

        $grade = $db->getAll('SELECT gr_id,gr_name FROM tang_grade WHERE 1');
        $grade = array_column($grade,'gr_name','gr_id');

        $data = array(
            'code' => '500104',
            'studyDirection' => F::array2Options($studyDirection),
            'grade' => F::array2Options($grade),
        );

		$this->setReplaceData($data);
		$this->setTempAndData();
        $this->show();
    }
}
