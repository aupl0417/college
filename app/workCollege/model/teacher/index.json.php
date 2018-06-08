<?php

class index_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);			
    }
    function run() {
		$dataGrid  = new DataGrid();
		$sql = "select id as DT_RowId,username,email,br_name as branchName,trueName,mobile,te_eduLevel,te_level,te_source as source,tl_name,tl_weight,if(te_isLeave=0,'在职','离职') as isLeave from tang_ucenter_member 
		    left join tang_branch on tangCollege=br_id 
		    left join tang_teacher on id=te_userId 
		    left join tang_teacher_level on te_level=tl_id 
		    where identityType=1";
		$data = $dataGrid->create($this->options, $sql);
		
		$db = new MySql(); 
		$source  = array(1=>'总部内训', '分院内训', '外聘教师');
		$opStr = '<a href="/teacher/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		foreach ($data['data'] as $key=>&$val) {
		    $classTable = $db->getAll('select cta_startTime,cta_endTime from tang_class_table where cta_teacherId="'.$val['DT_RowId'].'"');
		    $sum = 0;
		    foreach($classTable as $k=>$v){
		        $sum += (strtotime($v['cta_endTime']) - strtotime($v['cta_startTime']));
		    }
		    $val['teachHours'] = round($sum / 3600, 1);
		    $val['source'] = $source[$val['source']];
		    $val['op'] = sprintf($opStr,'detail',$val['DT_RowId'],'#temp-modal-power','fa-edit','详情').
		                 sprintf($opStr,'edit',$val['DT_RowId'],'#temp-modal-power','fa-edit','编辑').
		                 sprintf($opStr,'trainRecord',$val['DT_RowId'],'#temp-modal-power','fa-edit','培训记录').
		                 sprintf($opStr,'adjustLevel',$val['DT_RowId'],'#temp-modal-power','fa-edit','等级调整')
// 		                 sprintf($opStr,'certificateRecord',$val['DT_RowId'],'#temp-modal-power','fa-edit','证书记录')
		                 ;
		}
		
		echo json_encode($data);
    }
}
