<?php

class closeEnroll_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010503]);
    }

    public function run(){
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
            
        $id = $this->options['id'];
        $data = array(
            'tse_status' => -1,
        );
        $db = new MySql();
        $res = $db->update('tang_student_enroll', $data, 'tse_id="' . $id . '"');
        $res === false && die($this->show(message::getJsonMsgStruct('1002', '操作失败')));
        $this->show(message::getJsonMsgStruct('1001', '操作成功'));
    }
}
