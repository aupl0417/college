<?php

class promotion_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030104]);			
    }
    function run() {
		$dataGrid  = new DataGrid();
		$sql = "select tp_id as DT_RowId,trueName,te_level,a.tl_name as oriLevel,b.tl_name as proLevel,tp_createTime,tp_status as status from tang_teacher_promotion 
		    left join tang_teacher on tp_teacherId=te_userId 
		    left join tang_teacher_level a on te_level=a.tl_id 
		    left join tang_teacher_level b on tp_applyLevelId=b.tl_id 
		    left join tang_ucenter_member on tp_teacherId=id 
		    where 1=1";
		$data = $dataGrid->create($this->options, $sql);
		
		$db = new MySql();
		$opStr = '<a href="/teacher/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		foreach ($data['data'] as $key=>&$val) {
		    $val['op'] = '';
		    if($val['status'] == 0){
		        $val['op'] = sprintf($opStr,'review',$val['DT_RowId'],'#temp-modal-power','fa-edit','审核');
		    }
		    $val['status'] = $val['status'] == 0 ? '未审核' : ($val['status'] == 1 ? '审核通过' : '审核拒绝');
		} 
		
		echo json_encode($data);
    }
}
