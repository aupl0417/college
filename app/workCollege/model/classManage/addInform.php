<?php
/*=============================================================================
#     FileName: addInform.php
#         Desc: 发布通知
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:48:33
#      History:
#      Paramer:
=============================================================================*/
class addInform extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $data = array(
            'code' => '500103',
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
