<?php

class add extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040401]);
    }
	
    function run() {
        $data = array( 
            'code'          => 50040401,
            'tempId'		=> 'temp_'.F::getGID(),
        );
        
        $sql = "select a_code as id, a_name as name from tang_area where a_fkey=0";
        $db  = new MySql();
        $provinceList = $db->getAll($sql);
        
        $this->setLoopData('provinceList', $provinceList);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
