<?php
/*=============================================================================
#     FileName: viewQcode.php
#         Desc: 课程二维码
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:49:32
#      History:
#      Paramer:
=============================================================================*/
class viewQcode extends worker {
    function __construct($options) {
        parent::__construct($options, [500102]);
    }

    function run() {
        $options = $this->options;

        $data = array(
            'code' => '500102',
             'id' => intval($options['id']),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
