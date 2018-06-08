<?php
/*=============================================================================
#     FileName: searchStudent.json.php
#         Desc: 搜索添加学员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:47:31
#      History:
#      Paramer:
=============================================================================*/
class searchStudent_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;
        if (!isset($options['value']) || empty($options['value'])) {
            die($this->show(message::getJsonMsgStruct(1002,'请填写用户名或手机号')));
        }

        $options['value'] = trim($options['value']);
        $db = new MySql();
        $sql = "SELECT username,mobile FROM tang_ucenter_member WHERE (username='{$options['value']}' OR mobile='{$options['value']}') AND identityType=0";
        $info = $db->getRow($sql);

        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct(1002,'没有找到该学员信息')));
        }

        die($this->show(message::getJsonMsgStruct(1001,$info)));
    }
}
