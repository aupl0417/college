<?php

class feedback_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040503]);			
    }
	
    function run() {
		$dataGrid  = new DataGrid();
		$sql = "select f_id as DT_RowId,f_content as content,f_createTime,username from tang_feedback 
		    left join tang_ucenter_member on f_userId=id 
		    where f_type=0";
		$data = $dataGrid->create($this->options, $sql);
		$opStr = '<a href="/comment/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        
		$db = new MySql();
		foreach ($data['data'] as $key=>&$val) {
		    $count = $db->getField('select count(f_id) from tang_feedback where f_replyId="' . $val['DT_RowId'] . '"');
		    $val['isReply'] = $count ? '是' : '否';
		    $val['op'] = sprintf($opStr,'fdetail', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '详情');
		    if(!$count){
		        $val['op'] .= sprintf($opStr,'reply', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '回复');
		    }
		}
		
		echo json_encode($data);
    }
}
