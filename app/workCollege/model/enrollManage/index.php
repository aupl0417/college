<?php
/*=============================================================================
#     FileName: index.php
#         Desc: 发布报名记录
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-18 20:29:48
#      History:
#      Paramer:
=============================================================================*/
class index extends worker {
    function __construct($options) {
        parent::__construct($options, [50010303]);
    }

    function run() {
        $data = array(
            'code' => '50010303',
        );

        $db = new MySql();
        $studyDirection         = $db->getAll('SELECT sd_id,sd_name FROM tang_study_direction WHERE 1');
        $studyDirection         = array_column($studyDirection,'sd_name','sd_id');

        $grade = $db->getAll('SELECT gr_id,gr_name FROM tang_grade WHERE 1');
        $grade = array_column($grade,'gr_name','gr_id');

        $data['studyDirection'] = F::array2Options($studyDirection);
        $data['grade']          = F::array2Options($grade);
        $data['stateList'] =  F::array2Options(array('待审核','通过','不通过'));

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
