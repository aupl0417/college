<?php
/*=============================================================================
#     FileName: delayDeal.json.php
#         Desc: 延迟处理报到
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-15 20:27:53
#      History:
#      Paramer:
=============================================================================*/
class delayDeal_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500105]);
    }

    function run() {
        $options = $this->options;

        if (!isset($options['clID']) || empty($options['clID'])) {
            die($this->show(message::getJsonMsgStruct(1002,'参数错误')));
        }

        $cache = new cache();
        $key = 'checkInScan_'.$_SESSION['userID']."_{$options['clID']}";

        $cache->del($key);

        die($this->show(message::getJsonMsgStruct(1001,'操作成功')));
    }
}
