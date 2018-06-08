<?php

class application_json extends guest {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [8]);
		$this->db = new MySql();
    }

    function run() {
		$dataGrid = new DataGrid();


		$sql = "SELECT * from t_develop_application where 1 ";
		$result = $dataGrid->create($this->options, $sql);


		if($result['data']){
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['da_id'];

				$result['data'][$k]['da_dp_id']		 = $this->db->getField("select u.u_nick from t_develop_partner as p left join t_user as u on p.dp_uid=u.u_id where p.dp_id = '".$v['da_dp_id']."'");

				if($result['data'][$k]['da_class']==1){
					$result['data'][$k]['da_class'] = 'web网页应用';
				}
				if($result['data'][$k]['da_class']==2){
					$result['data'][$k]['da_class'] = 'app移动应用';
				}
				if($result['data'][$k]['da_class']==3){
					$result['data'][$k]['da_class'] = '硬件接入应用';
				}


				if($result['data'][$k]['da_status']==1){
					$result['data'][$k]['da_status'] = '审核中';
					$result['data'][$k]['op'] = '<a href="/open/editApplication/?_ajax=1&id='. $v['da_id'] .'" data-target="#editApplication" data-toggle="modal" class="btn-xs blue"><i class="fa fa-search"></i> 查看详情</a><a href="/open/checkApplication/?_ajax=1&id='. $v['da_id'] .'" data-target="#cheakApplication" data-toggle="modal" class="btn-xs blue"><i class="fa fa-edit"></i> 审核</a>';
				}

				if($result['data'][$k]['da_status']==2){
					$result['data'][$k]['da_status'] = '<span style="color: forestgreen">审核成功</span>';
					$result['data'][$k]['op'] = '<a href="/open/editApplication/?_ajax=1&id='. $v['da_id'] .'" data-target="#editApplication" data-toggle="modal" class="btn-xs blue"><i class="fa fa-search"></i> 查看详情</a>';
				}

				if($result['data'][$k]['da_status']==3){
					$result['data'][$k]['da_status'] = '审核失败';
					$result['data'][$k]['op'] = '<a href="/open/editApplication/?_ajax=1&id='. $v['da_id'] .'" data-target="#editApplication" data-toggle="modal" class="btn-xs blue"><i class="fa fa-search"></i> 查看详情</a>';
				}

				if($result['data'][$k]['da_status']==4){
					$result['data'][$k]['da_status'] = '已删除';
					$result['data'][$k]['op'] = '<a href="/open/editApplication/?_ajax=1&id='. $v['da_id'] .'" data-target="#editApplication" data-toggle="modal" class="btn-xs blue"><i class="fa fa-search"></i> 查看详情</a>';
				}
			}
		}
		echo json_encode($result);
    }

}