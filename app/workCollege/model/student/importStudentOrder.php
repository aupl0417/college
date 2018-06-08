<?php
/*=============================================================================
#     FileName: importStudentOrder.php
#         Desc: 导入学员报名订单
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-27 09:03:03
#      History:
#      Paramer:
=============================================================================*/

class importStudentOrder extends worker {
    function __construct($options) {
        parent::__construct($options, [50020101]);
    }

    function run() {
		$data = array(
            'jsData' => json_encode(array('id'=>63)),
			'tempId'		=> 'temp_'.F::getGID(),
		);

		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
