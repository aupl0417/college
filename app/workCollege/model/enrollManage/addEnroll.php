<?php
/*=============================================================================
#     FileName: addEnroll.php
#         Desc: 发布报名
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-12-20 11:03:01
#      History:
#      Paramer:
=============================================================================*/
class addEnroll extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $db             = new MySql();
        $studyDirection = $db->getAll('SELECT sd_id,sd_name FROM tang_study_direction WHERE 1');
        $studyDirection = array_column($studyDirection,'sd_name','sd_id');

        $courseTemplate = $db->getAll('SELECT ctt_id,ctt_name FROM tang_class_table_templet WHERE ctt_state=1');
        $courseTemplate = array_column($courseTemplate,'ctt_name','ctt_id');

        $course = $db->getAll('SELECT co_name,co_id FROM tang_course WHERE co_state=1');
        $course = array_column($course,'co_name','co_id');

        $branch = $db->getAll('SELECT br_id,br_name FROM tang_branch where br_state=1');
        $branch = array_column($branch,'br_name','br_id');

        $headmaster = $db->getAll('SELECT id,trueName FROM tang_ucenter_member WHERE identityType=1');
        $headmaster = array_column($headmaster,'trueName','id');

        $catering = F::getAttrs(1);

        $catering = F::getAttrs(1);
        $hostel   = F::getAttrs(2);

        $data = array(
            'code'           => '500105',
            'number'         => $db->getField('SELECT MAX(cl_number)+1 FROM tang_class'),
            'studyDirection' => F::array2Options($studyDirection),
            'courseTemplate' => F::array2Options($courseTemplate),
            'headmaster'     => F::array2Options($headmaster),
            'branch'         => F::array2Options($branch),
            'course'         => F::array2Options($course),
            'catering'       => F::array2Options($catering),
            'hostel'         => F::array2Options($hostel),
        );

        $codition = $db->getAll('SELECT stc_id,stc_name FROM tang_study_condition WHERE stc_state=1');

		$this->setReplaceData($data);
        $this->setLoopData('condition',$codition);
		$this->setTempAndData();
        $this->show();
    }
}
