<?php
/*=============================================================================
#     FileName: checkArrivalUser.json.php
#         Desc: 获取报到学员账号
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-07 20:11:04
#      History:
#      Paramer: 
=============================================================================*/

class checkArrivalUser_json extends worker{
    public function run(){
        $options = $this->options;

        if (!isset($options['mobile']) || empty($options['mobile'])) {
            die($this->show(message::getJsonMsgStruct('1002','手机号错误' )));
        }

        $userInfo = apis::request('/college/api/getUser.json',['mobile'=>$options['mobile'],'sync'=>1],true);

        if (1001 != $userInfo['code']) {
            die($this->show(message::getJsonMsgStruct('1002', $userInfo['data'])));
        }

        $userInfo = $userInfo['data'];
        $res['username'] = $userInfo['nick'];

        //$user = $db->getRow("SELECT username,level,auth FROM tang_ucenter_member WHERE mobile='{$options['mobile']}'");
        
        if($userInfo['level'] < 3 || substr($userInfo['auth'], 2, 1) != '1'){
           $res['msg'] = '<font style="color:red;"><b>该会员不是创客以上会员或者身份证认证未通过<b></font>';
           $res['state'] = 0;
        }else {
           $res['msg'] = '<font style="color:green;"><b>符合转让条件<b/></font>';
           $res['state'] = 1;
        }
        
        die($this->show(message::getJsonMsgStruct('1001', $res)));
    }
}
