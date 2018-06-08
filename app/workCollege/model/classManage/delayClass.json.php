<?php
/*=============================================================================
#     FileName: delayClass.json.php
#         Desc: 延迟开课
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:47:53
#      History:
#      Paramer:
=============================================================================*/
class delayClass_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options   = $this->options;
        $needParam = array(
            'clID'      => '班级ID',
            'startTime' => '开始时间',
            'endTime'   => '结束时间',
        );

        foreach ($needParam as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}参数错误")));
            }
        }

        if (strtotime($options['startTime']) > strtotime($options['endTime'])) {
            die($this->show(message::getJsonMsgStruct(1002,"时间设置错误")));
        }

        $updData = array(
            'cl_startTime' => $options['startTime'],
            'cl_endTime'   => $options['endTime'],
        );

        $db = new MySql();
        $res = $db->update('tang_class',$updData,"cl_id='{$options['clID']}'");

        if (1 != $res) {
            die($this->show(message::getJsonMsgStruct(1002,"延迟开课失败")));
        }

        die($this->show(message::getJsonMsgStruct(1001,'延迟开课成功')));
    }
}
