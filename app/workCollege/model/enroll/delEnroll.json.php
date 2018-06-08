<?php

class delEnroll_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010503]);
    }

    public function run(){
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        
        $id = $this->options['id'];
        
        $res = apis::request('college/api/deleteEnroll.json', ['id' => $id], true);
        
        if($res['code'] != '1001'){
            $this->show(message::getJsonMsgStruct('1002', '删除失败'));
        }else {
            $this->show(message::getJsonMsgStruct('1001', '删除成功'));
        }
    }
}
