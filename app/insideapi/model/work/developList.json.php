<?php

class developList_json extends guest {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [8]);
		$this->db = new MySql();
    }

    function run() {
		$dataGrid = new DataGrid();


		$sql = "SELECT * from t_develop_partner where 1 ";
		$result = $dataGrid->create($this->options, $sql);//获取数据


		if($result['data']){
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['dp_id'];

				if($v['dp_status'] == 0){
					$result['data'][$k]['dp_status'] = '待审核';
				}
				if($v['dp_status'] == 1){
					$result['data'][$k]['dp_status'] = '正常';
				}
				if($v['dp_status'] == 2){
					$result['data'][$k]['dp_status'] = '冻结';
				}
				if($v['dp_status'] == 3){
					$result['data'][$k]['dp_status'] = '禁用';
				}
				if($v['dp_status'] == 4){
					$result['data'][$k]['dp_status'] = '拒绝';
				}

				$result['data'][$k]['dp_uid'] = $this->db->getField("select u_nick from t_user where u_id = '".$v['dp_uid']."'");


				$result['data'][$k]['op'] = '<a href="/open/editdevelop/?_ajax=1&id='. $v['dp_id'] .'" data-target="#edit_develop" data-toggle="modal" class="btn-xs blue"><i class="fa fa-edit"></i> 审核&编辑</a>';
			}
		}
		echo json_encode($result);
    }

}
