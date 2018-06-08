<?php
/*=============================================================================
#     FileName: addStudentCertNum.json.php
#         Desc: 添加学员身份证号 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-26 19:48:40
#      History:
#      Paramer: 
=============================================================================*/

class addStudentCertNum_json extends worker {
    function __construct($options) {
        parent::__construct($options, ['500105']); 
    }
    
    function run() {
        $options = $this->options;
        
        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct('1002', '学员id错误')));
        }

        if (!isset($options['certNum']) || empty($options['certNum'])) {
            die($this->show(message::getJsonMsgStruct('1002', '身份证信息错误')));
        }

        if (!isset($options['trueName']) || empty($options['trueName'])) {
            die($this->show(message::getJsonMsgStruct('1002', '真实姓名错误')));
        }

        $db = new MySql();
        $updData = array(
            'certNum'=>trim($options['certNum']),
            'trueName'=>trim($options['trueName'])
        );
        $res = $db->update('tang_ucenter_member',$updData,"id={$options['id']}");

        if (1 != $res) {
            die($this->show(message::getJsonMsgStruct('1001', '添加失败')));
        }

	    die($this->show(message::getJsonMsgStruct('1001', '添加成功')));
    }
}
