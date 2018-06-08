<?php

class review_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040401]);
    }
    
    function run() {
        
        if(empty($this->options['id'])){
            $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
        }
        
        if(empty($this->options['state'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择审核落地'));exit;
        } 
        
        if(empty($this->options['reason'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写理由'));exit;
        }
        
        $data = array(
            'br_state'    => $this->options['state'] + 0,
            'br_reason'   => $this->options['reason']
        );
        
        $db = new MySql();
        $id = $this->options['id'] + 0;
        $res = $db->update('tang_branch', $data, 'br_id="' . $id . '"');
        if($res === false){
            $this->show(message::getJsonMsgStruct('1002', '操作失败'));exit;
        }
        
        $this->show(message::getJsonMsgStruct('1001', '操作成功'));exit;
    }
    
}
