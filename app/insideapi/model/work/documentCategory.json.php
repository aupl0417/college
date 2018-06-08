<?php

class documentCategory_json extends guest {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [8]);
		$this->db = new MySql();

		
    }

    function run() {
		$dataGrid = new DataGrid();

		$sql = "SELECT * from t_document_category";
		$result = $dataGrid->create($this->options, $sql);//获取数据


        //dump($result);

		if($result['data']){

			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['dc_id'];//这一行是必须加上的,用于给table的tr加上标识;请使用主键或者unqiue字段
				$result['data'][$k]['dc_num']		 = $this->db->getField("select count(*) from t_document_list where dl_dc_id = '".$v['dc_id']."'");

				$result['data'][$k]['op'] = '<a href="/open/editDocumentCategory/?_ajax=1&id='. $v['dc_id'] .'" data-target="#editDocumentCategory" data-toggle="modal" class="btn-xs blue"><i class="fa fa-edit"></i> 修改顺序</a><a href="javascript:;" onclick="javascript:deleteDocumentCategory('.$v['dc_id'].');"><i class="fa fa-trash"></i> 移除</a>';
			}
		}
		echo json_encode($result);
    }

}
