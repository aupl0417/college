<?php

class setLevel_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030103]);			
    }
    function run() {
// 		dump($this->options);die;
		$dataGrid  = new DataGrid();
		$sql = "select tl_id as DT_RowId,tl_name,tl_badgeName,tl_logo,tl_courseLevel,tl_courseType,tl_condition from tang_teacher_level where 1=1";
		$data = $dataGrid->create($this->options, $sql);
		
		$opStr = '<a href="/teacher/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		
		$courseLevel = array(1 => '初级', 2 => '中级课及以下', 3 => '高级课及以下', 4 => '其它课', 5 => '所有等级');
		$courseType  = array('公开课', '定制课', '其它');
		$db = new MySql();
		foreach ($data['data'] as $key=>&$val) {
		    $val['tl_courseLevel'] = $courseLevel[$val['tl_courseLevel']];
		    $val['tl_courseType']  = $courseType[$val['tl_courseType']];
		    $val['op'] = sprintf($opStr,'view',$val['DT_RowId'],'#temp-modal-power','fa-edit','查看').
		                 sprintf($opStr,'editLevel',$val['DT_RowId'],'#temp-modal-power','fa-edit','编辑').
		                 '<a class="btn-xs blue" onclick="delStudent('.$val['DT_RowId'].');"><i class="fa fa-edit"></i> 删除</a>';
		}
		
		echo json_encode($data); 
    }
}
