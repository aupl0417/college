<?php

class index extends worker {

    function __construct($options) {        		
        parent::__construct($options, [60101]);			
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
					'person' => [2],
				),
				'1' => array(
					'mobile'  => [0],
					'email'	  => [1],
					'company' => [2],
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
