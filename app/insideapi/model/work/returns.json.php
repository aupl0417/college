<?php

class returns_json extends guest {
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

        $sql = "select * from t_interface_response_field where irf_il_id = '".$this->options['id']."'";

		$result = $dataGrid->create($this->options, $sql);//获取数据

		if($result['data']){
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['irf_id'];
				$result['data'][$k]['op'] = '<a href="javascript:void(0);" onclick="javascript:deleterequest('.$v["irf_id"].');"><i class="fa fa-trash"></i> 移除</a>';
			}
		}

		echo json_encode($result);
    }

}
