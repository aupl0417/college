<?php
/*=============================================================================
#     FileName: addStudent.php
#         Desc: 添加学员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:48:23
#      History:
#      Paramer:
=============================================================================*/
class addStudent extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['clID']) || empty($options['clID'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $data = array(
            'code'    => '500103',
            'clID' => $options['clID'],
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
