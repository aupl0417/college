<?php

class documentList_json extends guest {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [8]);
		$this->db = new MySql();
    }

    function run() {
		$dataGrid = new DataGrid();


		$sql = "SELECT * from t_document_list where 1 ";
		$result = $dataGrid->create($this->options, $sql);//获取数据


		if($result['data']){
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['dl_id'];

				$result['data'][$k]['op'] = '<a href="?return=/open/editDocument/'.urlencode('?id='.$v['dl_id']).'&root=8" class="btn-xs blue"><i class="fa fa-edit"></i> 修改编辑</a><a href="javascript:void(0);" onclick="javascript:complete('.$v["dl_id"].');"><i class="fa fa-trash"></i> 移除</a>';
			}
		}
		echo json_encode($result);
    }

}
