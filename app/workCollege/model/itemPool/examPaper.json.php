<?php

class examPaper_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040302]);			
    }
    //暂时不做
    function run() {
        
		$dataGrid  = new DataGrid();
		$sql = "select cep_id as DT_RowId,cep_name,cep_courseId,cep_userId,cep_totalScore,cep_isPublic,cep_updateTime,cep_createTime,co_name,username from tang_course_exam_paper  
		    left join tang_course on co_id=cep_courseId 
		    left join tang_ucenter_member on cep_userId=id 
		    where 1=1";
		$data = $dataGrid->create($this->options, $sql);
		$opStr = '<a href="/itemPool/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		
		foreach ($data['data'] as $key=>&$val) {
		    if($val['cep_isPublic'] == 0){
		        $type = 0;
		        $menuName = '共享';
		    }else if($val['cep_isPublic'] == 1){
		        $type = 1;
		        $menuName = '取消共享';
		    }
		    $val['cep_isPublic'] = $val['cre_isPublic'] == 1 ? '已共享' : '不共享';
		    
		    $val['op'] = '<a data-toggle="modal" onclick="shareHandle(' . $val['DT_RowId'] . ', '.$type.');" class="btn-xs blue"><i class="fa fa-edit"></i> '.$menuName.'</a>'.
		                 sprintf($opStr,'viewPaper', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '预览').
		                 sprintf($opStr,'editPaper', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '编辑').
		                 '<a data-toggle="modal" onclick="delPaper(' . $val['DT_RowId'] . ');" class="btn-xs blue"><i class="fa fa-edit"></i> 删除</a>'
		                 ;
		}
		
		echo json_encode($data);
    }
}
