<?php
/*=============================================================================
#     FileName: searchTeacher.json.php
#         Desc: 搜索导入教师
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:50:44
#      History:
#      Paramer:
=============================================================================*/
class searchTeacher_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50030105]);
    }

    function run() {
        $options = $this->options;
        if (!isset($options['value']) || empty($options['value'])) {
            die($this->show(message::getJsonMsgStruct(1002,'请填写用户名或手机号')));
        }

        $fromType = isset($options['type']) ? intval($options['type']) : 1;

        $sdk = new openSdk();
        switch($fromType){
        case 1:
            $info = $this->fromEmployee($sdk,$options['value']);
            break;
        case 2: 
            $info = $this->fromUser($sdk,$options['value']);
            break;
        }

        if (!$info) {
            die($this->show(message::getJsonMsgStruct(1002,'没有找到信息')));
        }

        //判断教师是否存在
        $db = new MySql();
        $isExist = $db->count('tang_ucenter_member',"userId='{$info['id']}'");

        if ($isExist>0) {
            die($this->show(message::getJsonMsgStruct(1002,'该老师信息已经存在，无需重复添加')));
        }

        //权限判断
        if (isset($info['powerList']) && 'all' != $info['powerList'] && !in_array(50,explode(',',$info['powerList']))) {
            die($this->show(message::getJsonMsgStruct(1002,'该雇员没有唐人大学权限，不可导入该讲师')));
        }

        die($this->show(message::getJsonMsgStruct(1001,$info)));
    }

    private function fromEmployee($sdk,$value){
        $param = array('input'=>$value);
        $info = $sdk->request($param, 'user/getEmployee');
        if ('SUCCESS' != $info['id']) {
            return false;
        }
        $info         = $info['info'];
        $info['nick'] = $info['id'];
        return $info;
    }

    private function fromUser($sdk,$value){
        $param = array('nick_mobile'=>$value);
        $info = $sdk->request($param, '/user/getUserInfoByMobileOrNick');
        if ('SUCCESS' != $info['id']) {
            return false;
        }

        return $info['info'];
    }
}
