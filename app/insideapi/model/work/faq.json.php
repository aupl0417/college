<?php

class faq_json extends guest {

    function __construct($options) {        		
        parent::__construct($options, [60110]);			
    }
    function run() {
		$dataGrid = new DataGrid();
		$sql = "SELECT * from t_question_answer where 1";
		$result = $dataGrid->create($this->options, $sql);//获取数据
		if($result['data']){
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['qa_id'];

				$result['data'][$k]['op'] = '<a href="?return=/open/editFaq/'.urlencode('?id='.$v['qa_id']).'&root=8&id='. $v['qa_id'] .'" class="btn-xs blue"><i class="fa fa-edit"></i> 修改编辑</a><a href="javascript:void(0);" onclick="javascript:complete('.$v["qa_id"].');"><i class="fa fa-trash"></i> 移除</a> ';
			}
		}
		echo json_encode($result);
    }
}
