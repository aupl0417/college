<?php

class index extends worker {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [401]);	
		$this->db = new MySql();			
    }
    function run() {
		$options = $this->options;
		$type = isset($options['type']) ? (F::fmtNum($options['type']) - 0) : 0;
		$info = array();
		//t_company_reward_rule
		$sql = "select ri_id, ri_L1, ri_L2, ri_L3, ri_L4, ri_L5, ri_L6, ri_L7 from t_company_reward_rule order by ri_id asc";
		$result['crr'] = $this->db->getAll($sql);
		
		//company_struct
		$sql = "select * from t_company_struct order by cs_level asc";
		$result['cs'] = $this->db->getAll($sql);
		
		//t_company_product
		$sql = "select * from t_company_product order by cp_id asc";
		$result['cp'] = $this->db->getAll($sql);
		
		//t_reward_type
		$sql = "select * from t_reward_type order by rt_id asc";
		$result['rt'] = $this->db->getAll($sql);
		
		//t_reward_awards
		$sql = "select * from t_reward_awards order by ra_id asc";
		$result['ra'] = $this->db->getAll($sql);
		
		$data = array(
			'jsData' => json_encode($result),
		    'code'   => 401,
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
	
}
