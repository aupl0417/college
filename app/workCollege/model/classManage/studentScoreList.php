<?php
/*=============================================================================
#     FileName: studentScoreList.php
#         Desc: 学员成绩列表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:47:22
#      History:
#      Paramer:
=============================================================================*/
class studentScoreList extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['clID']) || empty($options['clID'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $data = array(
            'code' => '500103',
            'clID' => $options['clID'],
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
