<?php
/*=============================================================================
#     FileName: index.php
#         Desc: 课程列表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:46:42
#      History:
#      Paramer:
=============================================================================*/
class index extends worker {
    function __construct($options) {
        parent::__construct($options, [50010401]);
    }

    function run() {
        $data = array(
            'code' => '50010401',
        );

        $db = new MySql();
        $studyDirection         = $db->getAll('SELECT sd_id,sd_name FROM tang_study_direction WHERE 1');
        $studyDirection         = array_column($studyDirection,'sd_name','sd_id');

        $grade = $db->getAll('SELECT gr_id,gr_name FROM tang_grade WHERE 1');
        $grade = array_column($grade,'gr_name','gr_id');

        $data['studyDirection'] = F::array2Options($studyDirection);
        $data['grade']          = F::array2Options($grade);

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
