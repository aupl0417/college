<?php

class addEmployee extends worker {

    function __construct($options) {        		
        parent::__construct($options, [500501]);			
    }
    function run() {
        $db = new MySql();
        $result = $db->getAll("select dt_id, dt_name from t_duty");
        $data = array_column($result,'dt_name','dt_id');
//         dump($data);die;
        $duty = F::array2Options($data);
		$info = array();
		$msg = array(
			'jsData' => json_encode($info),
		    'code'   => 20205,
		    'duty'   => $duty,
			'tempId' => 'temp_'.F::getGID(),	
		);
// 		dump($msg);die;
		$this->setReplaceData($msg);
        $this->setTempAndData();
        $this->show();
    }
}
