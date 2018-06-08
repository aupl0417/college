<?php
/*=============================================================================
#     FileName: addCourseTemplate.php
#         Desc: 添加课程模板
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:45:45
#      History:
#      Paramer:
=============================================================================*/
class addCourseTemplate extends worker {
    function __construct($options) {
        parent::__construct($options, [50010402]);
    }

    function run() {
        $db             = new MySql();
        $studyDirection = $db->getAll('SELECT sd_id,sd_name FROM tang_study_direction WHERE 1');
        $studyDirection = array_column($studyDirection,'sd_name','sd_id');

        $grade = $db->getAll('SELECT gr_id,gr_name FROM tang_grade WHERE 1');
        $grade = array_column($grade,'gr_name','gr_id');

        $course = $db->getAll('SELECT co_name,co_id FROM tang_course WHERE co_state=1');
        $course = array_column($course,'co_name','co_id');

        $data = array(
            'code'           => '50010402',
            'studyDirection' => F::array2Options($studyDirection),
            'grade'          => F::array2Options($grade),
            'course'         => F::array2Options($course),
        );

        $codition = $db->getAll('SELECT stc_id,stc_name FROM tang_study_condition WHERE stc_state=1');

        $this->setReplaceData($data);
        $this->setLoopData('condition',$codition);
		$this->setTempAndData();
        $this->show();
    }
}
