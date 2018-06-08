<?php

class getClassInfo_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010503]);
    }

    public function run(){
        $db = new MySql();
        empty($this->options['classId']) && die($this->show(message::getJsonMsgStruct('1002', '请选择班级')));
        
        $classId = $this->options['classId'];
        $sql = 'select cl_cost from tang_class where cl_id="' . $classId .'"';
        $classList = $db->getRow($sql);
        !$classList && die($this->show(message::getJsonMsgStruct('1002')));
        
        $this->show(message::getJsonMsgStruct('1001', $classList));
    }
}
