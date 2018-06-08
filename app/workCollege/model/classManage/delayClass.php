<?php
/*=============================================================================
#     FileName: delayClass.php
#         Desc: 延迟开课
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:47:49
#      History:
#      Paramer:
=============================================================================*/
class delayClass extends worker {
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

        $db = new MySql();
        $info = $db->getRow("SELECT cl_startTime startTime,cl_endTime endTime FROM tang_class WHERE cl_id='{$options['clID']}'");
        $data = array_merge($data,$info);

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
