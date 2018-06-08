<?php

class index_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040501]);			
    }
    //暂时不做
    function run() {
        
		$dataGrid  = new DataGrid();
		$sql = "select tc_id as DT_RowId,tc_classTableId,tc_createTime,cl_name,tc_classId,username,tc_isPublic from tang_teacher_comment 
		    left join tang_class on cl_id=tc_classId 
		    left join tang_ucenter_member on tc_userId=id 
		    where 1=1"; 
		$data = $dataGrid->create($this->options, $sql);
		$opStr = '<a href="/comment/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        
		$db = new MySql();
		foreach ($data['data'] as $key=>&$val) {
		    if($val['tc_isPublic'] == 0){
		        $type = 0;
		        $menuName = '共享';
		    }else if($val['tc_isPublic'] == 1){
		        $type = 1;
		        $menuName = '取消共享';
		    }
		    $val['co_name'] = '';
		    $val['type'] = '班级评论';
		    if(intval($val['tc_classTableId']) > 0){
		        $val['co_name'] = $db->getField('select co_name from tang_class_table left join tang_course on cta_courseId=co_id where cta_id="' . $val['tc_classTableId'] . '"');
		        $val['type'] = '课程评论';
		    }
		    
		    $val['tc_isPublic'] = $val['cr_isPublic'] == 1 ? '已共享' : '不共享';
		    
		    $val['op'] = '<a data-toggle="modal" onclick="shareHandle(' . $val['DT_RowId'] . ', '.$type.');" class="btn-xs blue"><i class="fa fa-edit"></i> '.$menuName.'</a>'.
		                 sprintf($opStr,'detail', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '详情').
		                 '<a data-toggle="modal" onclick="delComment(' . $val['DT_RowId'] . ');" class="btn-xs blue"><i class="fa fa-edit"></i> 删除</a>'
		                 ;
		}
		
		echo json_encode($data);
    }
}
