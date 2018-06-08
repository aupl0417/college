<?php

class indexNew extends worker {

    function __construct($options) {        		
        parent::__construct($options, [60110]);			
    }
    function run() {
		$db = new MySql();
		$userLevel = $db->getAll("select ul_id, ul_name from t_user_level order by ul_id asc");
		$userLevel = array_column($userLevel, 'ul_name', 'ul_id');
		
		$info = array(
			'authList' => array(
				'0' => array(
					'mobile' => [0],
					'email'	 => [1],
					'person' => [2,3,4,5,6],
				),
				'1' => array(
					'mobile'  => [0],
					'email'	  => [1],
					'company' => [2,3,4,5,6,7,8,9,10,11,12],
				)
			),
			'levels'	 => $userLevel,
			'companyTypes'	 => F::getAttrs(4)
		);
		$data = array(
			'jsData' 		=> json_encode($info),
		    'code'          => 601,
			'tempId'		=> 'temp_'.F::getGID(),
			'userLevel' 	=> F::array2Options($userLevel),	
			'companyTypes'  => F::array2Options(F::getAttrs(4, true), [], true),				
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
