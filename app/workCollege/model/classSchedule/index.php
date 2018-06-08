<?php
/*=============================================================================
#     FileName: index.php
#         Desc: 排课列表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:49:24
#      History:
#      Paramer:
=============================================================================*/
class index extends worker {
    function __construct($options) {
        parent::__construct($options, [500102]);
    }

    function run() {
        $data = array(
           'code' => '500102',
        );

        $db = new MySql();

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();

    }
}
