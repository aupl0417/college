<?php
/*=============================================================================
#     FileName: schedule.php
#         Desc: 排课列表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:47:35
#      History:
#      Paramer:
=============================================================================*/
class schedule extends worker {
    function __construct($options) {
        parent::__construct($options, [50010301]);
    }

    function run() {
        $data = array(
            'code' => '50010301',
            'clID' => $this->options['clID'],
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
