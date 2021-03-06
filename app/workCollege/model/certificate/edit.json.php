<?php

class edit_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040701]);			
    }
    //暂时不做
    function run() {
        
        if(empty($this->options['id'])){
            $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
        }
        
        if(empty($this->options['name'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写证书名称'));exit;
        }
        $name = $this->options['name'];
        
        if(empty($this->options['username'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写用户名'));exit;
        }
        $username = $this->options['username'];
        
        if(F::isEmpty($this->options['userType'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择用户类型'));exit;
        }
        $userType = $this->options['userType'] + 0;
        
        if(F::isEmpty($this->options['certType'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择证书类型'));exit;
        }
        $certType = $this->options['certType'] + 0;
        
        
        if(empty($this->options['condition'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择获得条件'));exit;
        }
        $condition = $this->options['condition'];
        
        
        
        $db = new MySql();
        $userId = $db->getField('select id from tang_ucenter_member where username="' . $username . '" and identityType="' . $userType . '"');
        !$userId && die($this->show(message::getJsonMsgStruct('1002', '用户不存在或者用户类型不正确')));
        
        $id = $this->options['id'] + 0;
        
        $time = time();
        $data = array(
            'tce_name'      => $name,
            'tce_userId'    => $userId,
            'tce_eId'       => $_SESSION['userID'],
            'tce_certType'  => $certType,
            'tce_userType'  => $userType, 
            'tce_condition' => $condition,
            'tce_updateTime'=> date('Y-m-d H:i:s', $time)
        );
        
        if(isset($this->options['logo']) && !empty($this->options['logo'])){
            $data['tce_url'] = $this->options['logo'];
        }
        
        $res = $db->update('tang_certificate', $data, 'tce_id="' . $id . '"');
        
        if($res === false){
            die($this->show(message::getJsonMsgStruct('1001', '编辑失败')));
        }
        
        $this->show(message::getJsonMsgStruct('1001', '编辑成功'));
    }
    
}
