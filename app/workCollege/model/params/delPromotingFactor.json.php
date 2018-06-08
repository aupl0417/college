<?php

class delPromotingFactor_json extends worker {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [40703]);	
		$this->db = new MySql();			
    }
    function run() {
		$options = $this->options;
		
		$id = isset($options['id']) ? trim($options['id']) - 0 : 0;
		$now = F::verifyDateTime(F::mytime());
		$now = $now->format('YmdHis') * 1000;
		if($now > $id){
			$this->show(message::getJsonMsgStruct('1002', '已生效不能删除'));
			exit;			
		}
		$type = $this->db->getField("SELECT pf_type FROM t_promoting_factor WHERE pf_id='".$id."'");
		if($type == ''){
			$this->show(message::getJsonMsgStruct('1002', '删除失败'));
		}
		if(!attrib::clearPromotingFactor($type)){
			$this->show(message::getJsonMsgStruct('1002', '删除失败'));
		};
		$where = " pf_id = '".$id."'";
		
		if($this->db->delete('t_promoting_factor', $where) == 1){
			
			$this->show(message::getJsonMsgStruct('1001'));
		}else{
			$this->show(message::getJsonMsgStruct('1002', '删除失败'));
		}
    }
	
}
