<?php

class index_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040301]);			
    }
    //暂时不做
    function run() {
        
		$dataGrid  = new DataGrid();
		$sql = "select cre_id as DT_RowId,cre_name,cre_description,cre_courseId,cre_userId,cre_isPublic,cre_updateTime,cre_createTime,co_name,username from tang_course_exam  
		    left join tang_course on co_id=cre_courseId 
		    left join tang_ucenter_member on cre_userId=id 
		    where 1=1";
		$data = $dataGrid->create($this->options, $sql);
		$opStr = '<a href="/itemPool/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		
		foreach ($data['data'] as $key=>&$val) {
		    if($val['cre_isPublic'] == 0){
		        $type = 0;
		        $menuName = '共享';
		    }else if($val['cre_isPublic'] == 1){
		        $type = 1;
		        $menuName = '取消共享';
		    }
		    $val['cre_isPublic'] = $val['cre_isPublic'] == 1 ? '已共享' : '不共享';
		    
		    $val['op'] = '<a data-toggle="modal" onclick="shareHandle(' . $val['DT_RowId'] . ', '.$type.');" class="btn-xs blue"><i class="fa fa-edit"></i> '.$menuName.'</a>'.
		                 sprintf($opStr,'view', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '预览').
		                 sprintf($opStr,'edit', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '编辑').
		                 '<a data-toggle="modal" onclick="delPaper(' . $val['DT_RowId'] . ');" class="btn-xs blue"><i class="fa fa-edit"></i> 删除</a>'
		                 ;
		}
		
		echo json_encode($data);
    }
}
