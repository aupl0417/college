<?php

class tree extends worker {	
	private $db;		
	function __construct($options) {		
        parent::__construct($options, [60108]);
		$this->db = new MySql();
    }

    function run() {		
		$id = isset($this->options['id']) ? $this->options['id'] : '';
		$parent = isset($this->options['parent']) ? ($this->options['parent'] - 0) : 0;
		if($id == '' || $id == '00000000000000000000000000000000'){
			$this->show(message::getJsonMsgStruct('1002','参数错误'));
			exit;
		}
		$level = 1;

		$type = $this->db->getField("select u_type from t_user where u_id = '".$id."'");
		if(!isset($type)){
			$this->show(message::getJsonMsgStruct('1002','参数错误'));
			exit;
		}

		if($type == 0){
			$family = $this->db->getRow("SELECT u_nick AS `name`, u_code AS `code` FROM t_user WHERE u_code = '".$parent."'");

			$user = $this->db->getRow("SELECT u_nick AS `name`, u_code AS `code` FROM t_user WHERE u_id = '".$id."'");
		}else{
			$family = $this->db->getRow("SELECT IF(u.u_type, uc.u_companyName, u.u_nick) AS `name`, u.u_code AS `code` FROM t_user AS u LEFT JOIN t_user_company AS uc ON u.u_id = uc.u_id WHERE u.u_code = '".$parent."'");

			$user = $this->db->getRow("SELECT IF(u.u_type, uc.u_companyName, u.u_nick) AS `name`, u.u_code AS `code` FROM t_user AS u LEFT JOIN t_user_company AS uc ON u.u_id = uc.u_id WHERE u.u_id = '".$id."'");
		}
		

		if(!$user || !$family){
			$this->show(message::getJsonMsgStruct('1002','参数错误'));
			exit;			
		};		
		$user['children'] = $this->familyTree($user['code'],$type);
		$family['children'][] = $user;
		$data = array(
			'jsData' => json_encode($family)
		);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();		

	}
	
	private function familyTree($parent, $type, $tree = array() ,$loop = 1){
		
//		$sql = "SELECT IF(u_type, u_companyName, u_nick) AS `name`, u_code AS `code` FROM t_user WHERE u_fCode = '".$parent."' and u_code > 0 limit 50";//u_id AS id,, u_fCode AS fCode, u_code AS `code`

		if($type == 0){
			$sql = "SELECT u_nick AS `name`, u_code AS `code` FROM t_user WHERE u_fCode = '".$parent."' and u_code > 0 limit 50";
		}else{
			$sql = "SELECT IF(u.u_type, uc.u_companyName, u.u_nick) AS `name`, u.u_code AS `code` FROM t_user AS u LEFT JOIN t_user_company AS uc ON u.u_id = uc.u_id WHERE u.u_fCode = '".$parent."' and u.u_code > 0 limit 50";
		}

		$children = $this->db->getAll($sql);
		if($children && $loop < 3){
			foreach($children as $k => $child){
				$children[$k]['children'] = self::familyTree($child['code'], $type, $tree ,$loop + 1);
			};
			return $children;
		}else{
			return $tree;
		}
	}
}
