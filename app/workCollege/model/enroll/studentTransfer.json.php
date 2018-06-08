<?php

class studentTransfer_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010503]);
    }

    public function run(){
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        (!isset($this->options['purId']) || empty($this->options['purId'])) && die($this->show(message::getJsonMsgStruct('1002', '您输入的用户信息不正确')));
        
        $id = $this->options['id'];
        $purId = $this->options['purId'] + 0;//被转让用户id
        
        $db = new MySql();
        $res = $db->getField('select count(tse_id) from tang_student_enroll where tse_id="' . $id . '" and tse_userId="' . $purId . '"');
        $res && die($this->show(message::getJsonMsgStruct('1002', '不能转给自已')));
        
        $userInfo = $db->getRow('select trueName,auth,level,certNum from tang_ucenter_member where id="' . $purId .'"');
        
        //!$userInfo['trueName'] && die($this->show(message::getJsonMsgStruct('1002', '该用户真实姓名为空')));
        $userInfo['level'] < 3 && die($this->show(message::getJsonMsgStruct('1002', '该用户不是创客以上用户')));
        $userInfo['auth'][2] == 0 && die($this->show(message::getJsonMsgStruct('1002', '该用户还未通过身份证认证')));
        
        $data = array(
            'tse_userTrueName' => $userInfo['trueName'],
            'tse_userId'       => $purId,
            'tse_certNum'      => $userInfo['certNum'],
        );
        
        $res = $db->update('tang_student_enroll', $data, 'tse_id="' . $id . '"');
        $res === false && die($this->show(message::getJsonMsgStruct('1002', '转让失败')));
        $this->show(message::getJsonMsgStruct('1001', '转让成功'));
    }
}
