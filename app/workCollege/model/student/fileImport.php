<?php
/*=============================================================================
#     FileName: fileImport.php
#         Desc: 文件导入学员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-05 10:37:32
#      History:
#      Paramer:
=============================================================================*/

class fileImport extends worker {
    function __construct($options) {
        parent::__construct($options, [500201]);
    }

    function run() {
        $db = new MySql();
        $classList = $db->getAll('SELECT cl_id,cl_name FROM tang_class WHERE cl_state<>-1 AND cl_status=1');
		$data = array(
		    'code'   => 500201,
			'tempId' => 'temp_'.F::getGID(),
            'classList' => F::array2Options(array_column($classList,'cl_name','cl_id')),
		);

		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
