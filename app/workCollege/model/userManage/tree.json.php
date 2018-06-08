<?php

class tree_json extends worker {	
	private $db;		
	function __construct($options) {		
        parent::__construct($options, [601]);
		$this->db = new MySql();
    }

    function run() {	
		$id = isset($this->options['id']) ? $this->options['id'] : '';
		//echo $id;
		if(F::fmtNum($id) === false){
			echo json_encode(array(
				'draw' => 0,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => [],			
			));			
			exit;
		}
		$dataGrid = new DataGrid();
		
		$sql = "select u.u_nick, u.u_name, uc.u_companyName, u.u_code, u.u_level, u.u_createTime, uc.u_isUnionSeller, uc.u_unionTime from t_user as u LEFT JOIN t_user_company as uc where u_fCode='".$id."'";

		$result = $dataGrid->create($this->options, $sql);//获取数据
		if($result){
			
		}else{
			
		}
		echo json_encode($result);


	}
	
}
