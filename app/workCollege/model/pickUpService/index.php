<?php

class index extends worker {
    function __construct($options) {
        parent::__construct($options, [50010506]);
    }

    function run() {
        $db = new MySql();
        $sql = "SELECT cl_id,cl_name FROM tang_class WHERE cl_status=1 AND cl_state IN(0,1) ORDER BY DATEDIFF(cl_startTime,CURDATE()) ASC";
        
        $classList = $db->getall($sql);
        $classList = array_column($classList,'cl_name','cl_id');
        
        $data = array(
            'code' => '50010506',
            'classList' => F::array2Options($classList),
        );
        
        $this->setReplaceData($data); 
        $this->setTempAndData();
        $this->show();
    }
}
