<?php

class interaction_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040502]);			
    }
    //暂时不做
    function run() {
        
		$dataGrid  = new DataGrid();
		$sql = "select tsi_id as DT_RowId,tsi_createTime,tsi_title,co_name,tsi_courseId,username,tsi_isPublic from tang_teacher_student_interaction 
		    left join tang_course on co_id=tsi_courseId 
		    left join tang_ucenter_member on tsi_userId=id 
		    where tsi_pid=0";
		$data = $dataGrid->create($this->options, $sql);
		$opStr = '<a href="/comment/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        $db = new MySql();
		foreach ($data['data'] as $key=>&$val) {
		    if($val['tsi_isPublic'] == 0){
		        $type = 0;
		        $menuName = '共享';
		    }else if($val['tsi_isPublic'] == 1){
		        $type = 1;
		        $menuName = '取消共享';
		    }
		    $val['replyCount'] = $db->getField('select count(tsi_id) from tang_teacher_student_interaction where tsi_pid="'.$val['DT_RowId'].'"');
		    $val['tsi_isPublic'] = $val['si_isPublic'] == 1 ? '已共享' : '不共享';
		    
		    $val['op'] = '<a data-toggle="modal" onclick="shareHandle(' . $val['DT_RowId'] . ', '.$type.');" class="btn-xs blue"><i class="fa fa-edit"></i> '.$menuName.'</a>'.
		                 sprintf($opStr,'detailInteraction', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '详情')
		                 //.'<a data-toggle="modal" onclick="delComment(' . $val['DT_RowId'] . ');" class="btn-xs blue"><i class="fa fa-edit"></i> 删除</a>'
		                 ;
		}
		
		echo json_encode($data);
    }
}
