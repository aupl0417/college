<?php
/*=============================================================================
#     FileName: addSite.php
#         Desc: 添加课程
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-29 16:33:33
#      History:
#      Paramer:
=============================================================================*/

class addSite extends worker {
    function __construct($options) {
        parent::__construct($options, [500401]);
    }

    function run() {
        $data = array(
            'code'         => '500401',
            'tempId'       => 'temp_'.F::getGID(),
            'jsData'       => json_encode(array(
                'property' => F::getAttrs(5),
                'typeList' => F::getAttrs(6),
            ))
        );

        $this->setReplaceData($data);
		$this->setTempAndData();
        $this->show();
    }
}
