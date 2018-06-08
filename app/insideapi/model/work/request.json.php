<?php

class request_json extends guest {
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

        $sql = "select * from t_interface_request_field where iqf_il_id = '".$this->options['id']."'";

		$result = $dataGrid->create($this->options, $sql);//获取数据

		if($result['data']){
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['iqf_id'];
				if($v['iqf_il_is_public'] == 1){
					$result['data'][$k]['iqf_il_is_public']		 = '是';
				}
				if($v['iqf_il_is_public'] == 2){
					$result['data'][$k]['iqf_il_is_public']		 = '否';
				}

				if($v['iqf_il_required'] == 1){
					$result['data'][$k]['iqf_il_required']		 = '是';
				}
				if($v['iqf_il_required'] == 2){
					$result['data'][$k]['iqf_il_required']		 = '否';
				}
				$result['data'][$k]['op'] = '<a href="javascript:void(0);" onclick="javascript:deleterequest('.$v["iqf_id"].');"><i class="fa fa-trash"></i> 移除</a>';
			}
		}

		echo json_encode($result);
    }

}
