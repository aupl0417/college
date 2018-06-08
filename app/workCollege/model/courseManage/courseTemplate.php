<?php
/*=============================================================================
#     FileName: courseTemplate.php
#         Desc: 课程模板
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:46:02
#      History:
#      Paramer:
=============================================================================*/
class courseTemplate extends worker {
    function __construct($options) {
        parent::__construct($options, [50010402]);
    }

    function run() {
        $db = new MySql();
        $grade = $db->getAll('SELECT gr_id,gr_name FROM tang_grade WHERE 1');
        $grade = array_column($grade,'gr_name','gr_id');

        $data = array(
            'code'      => '50010402',
            'stateList' => F::array2Options(array('-1' => '失效',1 => '有效')),
            'grade'     => F::array2Options($grade),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
