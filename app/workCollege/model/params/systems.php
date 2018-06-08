<?php

class systems extends worker {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [404]);	
		$this->db = new MySql();			
    }
    function run() {
		$options = $this->options;
		$type = isset($options['type']) ? (F::fmtNum($options['type']) - 0) : 0;
		$info = array();
		//t_company_reward_rule
		$sql = "select * from t_system order by sys_id asc";
		$result = $this->db->getAll($sql);

		$params = [];
		foreach($result as $v){
			$params[$v['sys_type']][$v['sys_id']] = [
				'value' => $v['sys_value'],
				'memo'  => $v['sys_memo'],
			];
		}
		$jsData = ['data' => $params];

		$data = array(
			'jsData' => json_encode($jsData),
		    'code'   => 404,
		);
		
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
	
}
