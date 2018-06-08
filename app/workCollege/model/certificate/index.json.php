<?php

class index_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040701]);			
    }
    //暂时不做
    function run() {
        
		$dataGrid  = new DataGrid();
		$sql = "select tce_id as DT_RowId,tce_name,if(tce_certType=1,'电子文档证书','纸质文档证书') as tce_certType,if(tce_userType=0,'学员','讲师') as tce_userType,CASE tce_condition WHEN 1 THEN '初级班级毕业' WHEN 2 THEN '中级班级毕业' ELSE '高级班级毕业' END as tce_condition,tce_createTime,tce_eId,trueName from tang_certificate 
		    left join tang_ucenter_member on tce_userId=id 
		    where 1=1";
		$data = $dataGrid->create($this->options, $sql);
		$opStr = '<a href="/certificate/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		
		$db  = new MySql();
// 		$condition = $db->getAll('select at_key as id,at_value as name from tang_attrib where at_type=8');
// 		$certType  = $db->getAll('select at_key as id,at_value as name from tang_attrib where at_type=7');
// 		$condition = array_column($condition, 'name', 'id');
// 		$certType =  array_column($certType, 'name', 'id');
		
		foreach ($data['data'] as $key=>&$val) {
// 		    $val['tce_certType'] = $certType[$val['tce_certType']];
// 		    $val['tce_condition'] = $condition[$val['tce_condition']];
// 		    $val['tce_userType'] = $val['tce_userType'] ? '讲师' : '学员';
		    $val['op'] = sprintf($opStr,'view', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '预览').
		                 sprintf($opStr,'edit', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '编辑').
		                 '<a data-toggle="modal" onclick="del(' . $val['DT_RowId'] . ');" class="btn-xs blue"><i class="fa fa-edit"></i> 删除</a>'
		                 ;
		    
		}
		
		echo json_encode($data);
    }
}
