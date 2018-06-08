<?php

class add_json extends worker {

    function __construct($options) {
        parent::__construct($options, [50040701]);			
    }
    
    function run() {
        
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
        
        if(empty($this->options['logo'])){
            $this->show(message::getJsonMsgStruct('1002',  '请上传证书'));exit;
        }
        $logo = $this->options['logo'];
        
        $db = new MySql();
        $userId = $db->getField('select id from tang_ucenter_member where username="' . $username . '"');
        !$userId && die($this->show(message::getJsonMsgStruct('1002', ($userType == 0 ? '学员' : '讲师') . '不存在')));
        
        $time = time();
        $data = array(
            'tce_name'      => $name,
            'tce_userId'    => $userId,
            'tce_eId'       => $_SESSION['userID'],
            'tce_url'       => $logo,
            'tce_certType'  => $certType,
            'tce_userType'  => $userType, 
            'tce_condition' => $condition,
            'tce_updateTime'=> date('Y-m-d H:i:s', $time),
            'tce_createTime'=> date('Y-m-d H:i:s', $time),
        );
        
        $id = $db->insert('tang_certificate', $data);
        
        if(!$id){
            die($this->show(message::getJsonMsgStruct('1001', '添加失败')));
        }
        
        $this->show(message::getJsonMsgStruct('1001', '添加成功'));
            
        
    }
}
