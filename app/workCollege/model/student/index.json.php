<?php

class index_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50020101]);			
    }
    
    function run() {
		$dataGrid  = new DataGrid();
		$sql = "select id as DT_RowId,username,email,br_name as branchName,mobile from tang_ucenter_member left join tang_branch on tangCollege=br_id where identityType=0";
		$data = $dataGrid->create($this->options, $sql);
		
		$db = new MySql();
		$opStr = '<a href="/student/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		foreach ($data['data'] as $key=>&$val) {
		    $classStudent = $db->getRow('select * from (select count(*) as classCount,sum(cs_score) as totalScore from tang_class_student where cs_studentId="'.$val['DT_RowId'].'") as a');
		    $val['classCount'] = $classStudent['classCount']; 
		    $val['totalScore'] = $classStudent['totalScore'] ? $classStudent['totalScore'] : 0;		
		    $val['op'] = sprintf($opStr,'view', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '详情').
		                 sprintf($opStr,'signUp', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '报名订单').
		                 sprintf($opStr,'studyRecord', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '学习记录');
		}
		
		echo json_encode($data);
    }
}
