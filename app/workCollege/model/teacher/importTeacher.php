<?php
/*=============================================================================
#     FileName: importTeacher.php
#         Desc: 导入教师
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:50:54
#      History:
#      Paramer:
=============================================================================*/
class importTeacher extends worker {
    function __construct($options) {
        parent::__construct($options, [50030105]);
    }

    function run() { 
        $db          = new MySql();
        $teacherLeve = $db->getAll('SELECT tl_id,tl_name FROM tang_teacher_level');
        $branch      = $db->getAll('SELECT br_id,br_name FROM tang_branch');
        $grade       = $db->getAll('SELECT gr_id,gr_name FROM tang_grade');

        $jsData = array(
            'education'    => F::getAttrs(3),
            'teacherFrom'  => F::getAttrs(4),
            'teacherLevel' => array_column($teacherLeve,'tl_name','tl_id'),
            'branch'       => array_column($branch,'br_name','br_id'),
            'grade'        => array_column($grade,'gr_name','gr_id'),
        );

		$data = array(
		    'code'   => 50030105,
            'jsData' => json_encode($jsData),
            'tempId' => F::getGID(),
		);

		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
