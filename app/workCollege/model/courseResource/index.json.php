<?php

class index_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);			
    }
    //暂时不做
    function run() {
        
		$dataGrid  = new DataGrid();
		$sql = "select cr_id as DT_RowId,cr_name,cr_description,cr_courseId,cr_readCount,cr_userId,cr_type,cr_branchId,cr_isPublic,cr_updateTime,cr_createTime,br_name as branchName,co_name from tang_course_resource  
		    left join tang_branch on cr_branchId=br_id 
		    left join tang_course on co_id=cr_courseId 
		    where 1=1";
		$data = $dataGrid->create($this->options, $sql);
		$opStr = '<a href="/courseResource/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		$op = '<a href="%s" target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
		$resourceType = array('文件文档', '视频', '网页文章');
		
		$db = new MySql();
		foreach ($data['data'] as $key=>&$val) {
		    
		    if(is_numeric($val['cr_userId'])){
		        $username = $db->getField('select username from tang_ucenter_member where id="' . $val['cr_userId'] . '"');
		        $val['username'] = $username ? $username : $_SESSION['userID'];
		    }else {
		        $val['username'] = $_SESSION['userID'];
		    }
		    
		    if($val['cr_isPublic'] == 0){
		        $type = 0;
		        $menuName = '共享';
		    }else if($val['cr_isPublic'] == 1){
		        $type = 1;
		        $menuName = '取消共享';
		    }
		    $val['cr_isPublic'] = $val['cr_isPublic'] == 1 ? '已共享' : '不共享';
		    
		    $val['op'] = '<a data-toggle="modal" onclick="shareHandle(' . $val['DT_RowId'] . ', '.$type.');" class="btn-xs blue"><i class="fa fa-edit"></i> '.$menuName.'</a>';
		    $val['op'] .= sprintf($opStr,'view', $val['DT_RowId'], '#formModal', 'fa-edit', '预览');
		    
		    if($val['cr_type'] != 2){
		        $val['op'] .= sprintf($opStr,'edit', $val['DT_RowId'], '#formModal', 'fa-edit', '编辑');
		    }
		    
		    $val['op'] .= '<a data-toggle="modal" onclick="delResource(' . $val['DT_RowId'] . ');" class="btn-xs blue"><i class="fa fa-edit"></i> 删除</a>'
		                 ;
		    $val['cr_type'] = $resourceType[$val['cr_type']];
		}
		
		echo json_encode($data);
    }
}
