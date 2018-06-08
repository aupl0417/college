<?php

class classTransfer_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010503]);
    }

    public function run(){
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数1')));
        (!isset($this->options['classId']) || empty($this->options['classId'])) && die($this->show(message::getJsonMsgStruct('1002', '请选择班级')));
        
        $id = $this->options['id'];
        $classId = $this->options['classId'] + 0;//要转的班级id
        
        $db = new MySql();
        $res = $db->getField('select count(tse_id) from tang_student_enroll where tse_id="' . $id . '" and tse_classId="' . $classId . '"');
        $res && die($this->show(message::getJsonMsgStruct('1002', '不能转到同一班级')));
        
        $data = array(
            'tse_classId'       => $classId
        );
        
        $res = $db->update('tang_student_enroll', $data, 'tse_id="' . $id . '"');
        $res === false && die($this->show(message::getJsonMsgStruct('1002', '转班失败')));
        $this->show(message::getJsonMsgStruct('1001', '转班成功'));
    }
}
