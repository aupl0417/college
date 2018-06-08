<?php

class category_json extends guest {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [8]);
		$this->db = new MySql();

		
    }

    function run() {

		if(!isset($_SESSION['userID'])){
			echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
		}

		$dataGrid = new DataGrid();

		$sql = "SELECT ic_id,ic_father_id,ic_name,ic_order from t_interface_category";
		$result = $dataGrid->create($this->options, $sql);//获取数据


        //dump($result);

		if($result['data']){

			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['ic_id'];//这一行是必须加上的,用于给table的tr加上标识;请使用主键或者unqiue字段

				$result['data'][$k]['op'] = '<a href="javascript:;" onclick="javascript:deleteCategory('.$v['ic_id'].');"><i class="fa fa-trash"></i> 移除</a>';
			}
		}
		echo json_encode($result);
    }

}
