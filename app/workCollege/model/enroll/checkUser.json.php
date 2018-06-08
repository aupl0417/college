<?php

class checkUser_json extends worker{
    
    public function run(){
        $options = $this->options;

//         if (!isset($options['trueName']) || empty($options['trueName'])) {
//             die($this->show(message::getJsonMsgStruct('1002','用户名错误' )));
//         }
        if (!isset($options['mobile']) || empty($options['mobile'])) {
            die($this->show(message::getJsonMsgStruct('1002','手机号错误' )));
        }
//         if (!isset($options['certNum']) || empty($options['certNum'])) {
//             die($this->show(message::getJsonMsgStruct('1002','身份证号码错误' )));
//         }
        if (!isset($options['userName']) || empty($options['userName'])) {
            die($this->show(message::getJsonMsgStruct('1002','用户名错误' )));
        }
        
        $db = new MySql();
        $where = 'username="' . $options['userName'] . '" and mobile="' . $options['mobile'] . '"';
        if(isset($options['trueName']) && !empty($options['trueName'])){
            $where .= ' and trueName="' . $options['trueName'] . '"';
        }
        
        if(isset($options['certNum']) && !empty($options['certNum'])){
            $where .= ' and trueName="' . $options['trueName'] . '"';
        }
        
        
        $user = $db->getField("SELECT level,auth FROM tang_ucenter_member WHERE '{$where}'");
        dump($user);die;
        if (empty($user)) {
            die($this->show(message::getJsonMsgStruct('1002', '该会员不存在，可先在【大唐天下APP】浏览班级信息后再试')));
        }
        die($this->show(message::getJsonMsgStruct('1001', $user)));
    }
}
