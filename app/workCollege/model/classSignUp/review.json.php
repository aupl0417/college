<?php

class review_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50010302]);
    }
    
    function run() {
        
        if(empty($this->options['id'])){
            $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
        }
        
        if(empty($this->options['state'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择开班审核'));exit;
        }
        
        if(empty($this->options['reason'])){
            $this->show(message::getJsonMsgStruct('1002',  '请填写理由'));exit;
        }
        
        $data = array(
            'cl_status'    => $this->options['state'] + 0,
            'cl_reason'   => $this->options['reason']
        );
        
        $db = new MySql();
        $id = $this->options['id'] + 0;
        
        $count = $db->getField('select count(cc_id) from tang_class_course where cc_classId="' . $id . '"');
        !$count && die($this->show(message::getJsonMsgStruct('1002',  '该班级还未排课')));
        
        $res = $db->update('tang_class', $data, 'cl_id="' . $id . '"');
        if($res === false){
            $this->show(message::getJsonMsgStruct('1002', '操作失败'));exit;
        }
        
        $this->show(message::getJsonMsgStruct('1001', '操作成功'));exit;
    }
    
}
