<?php

class getCount_json extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));

        $id = $this->options['id'] + 0;
        $where = 'cr_id="' . $id . '"';
        $readCount = $this->db->getField('select cr_readCount from tang_course_resource where ' . $where);
        $res = $this->db->update('tang_course_resource', ['cr_readCount' => $readCount + 1], $where);
        if($res === false){
            die($this->show(message::getJsonMsgStruct('1002')));
        }
        $this->show(message::getJsonMsgStruct('1001'));
    }

}
