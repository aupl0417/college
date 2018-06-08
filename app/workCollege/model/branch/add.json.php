<?php

class add_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040401]);
    } 
    
    function run() {
        
        if(empty($this->options['name'])){
            $this->show(message::getJsonMsgStruct('1002',  '机构名不能为空'));exit;
        }
        $name = $this->options['name'];
        
        if(empty($this->options['cityCode'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择城市'));exit;
        }
        $cityCode = $this->options['cityCode'];
        
        if(isset($this->options['contyCode']) && !empty($this->options['contyCode'])){
            $contyCode = $this->options['contyCode'];
        }
        
        if(empty($this->options['address'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写具体地址'));exit;
        }
        $address = $this->options['address'];
        
        
        $time = time();
        $data = array(
            'br_name'       => $name,
            'br_parentId'   => $cityCode + 0,
            'br_areaId'   => $contyCode + 0,
            'br_address'     => $address,
            'br_updateTime' => date('Y-m-d H:i:s', $time),
            'br_createTime' => date('Y-m-d H:i:s', $time),
        );
        
        $db = new MySql();
        $id = $db->insert('tang_branch', $data);
        if(!$id){
            $this->show(message::getJsonMsgStruct('1002', '添加失败'));exit;
        }
        
        $this->show(message::getJsonMsgStruct('1001', '添加成功'));exit;
    }
    
}
