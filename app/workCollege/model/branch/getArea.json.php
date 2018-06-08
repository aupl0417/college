<?php

class getArea_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040401]);			
    }
    
    function run() {
		$id = $this->options['id'];
		if(F::isEmpty($id)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		} 
		
		$db = new MySql();
		$result = $db->getAll('select a_code as id, a_name as name from tang_area where a_fkey=(select a_id from tang_area where a_code="'.intval($id).'")');
		
		$this->show(message::getJsonMsgStruct('1001',  $result));
    }
}
