<?php

class index_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040401]);			
    }
    //暂时不做
    function run() {
        
		$dataGrid  = new DataGrid();
		$sql = "select br_id as DT_RowId,br_areaId,br_addRess,br_name as branchName,br_level,br_state,br_updateTime,br_createTime,a_id,a_name as areaName from tang_branch  
		    left join tang_area on br_areaId=a_code 
		    where 1=1";
		$data = $dataGrid->create($this->options, $sql);
		$opStr = '<a href="/branch/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
// 		dump($data);die; 
		$resourceType = array('未审核', '审核通过', '审核拒绝');
		
		foreach ($data['data'] as $key=>&$val) {
		    $val['op'] = sprintf($opStr,'detail', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '详情')
		                 .sprintf($opStr,'edit', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '编辑')
		                 .'<a onclick="delBranch(' . $val['DT_RowId'] . ')" class="btn-xs blue"><i class="fa %s"></i> 删除</a>';
		    if($val['br_state'] == 0){
		        $val['op'] .= sprintf($opStr,'review', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '审核落地');
		    }
		                 
		                 //'<a data-toggle="modal" onclick="delBranch(' . $val['DT_RowId'] . ');" class="btn-xs blue"><i class="fa fa-edit"></i> 删除</a>'
		                 ;
		    $val['br_state'] = $resourceType[$val['br_state']];
		}
		
		echo json_encode($data);
    }
}
