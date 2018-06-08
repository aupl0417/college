<?php

class category extends guest {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [8]);
		$this->db = new MySql();

		
    }

    function run() {

        /*$sql = "select * from t_interface_category";
        $categoryData = $this->db->getAll($sql);
		$data = array(
			'categoryData' => $categoryData,
		);
        $this->setReplaceData($data);*/

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }

        $this->setTempAndData();
        $this->show();
    }

}
