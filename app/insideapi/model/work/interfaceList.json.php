<?php

class interfaceList_json extends guest {
	private $db;
    function __construct($options) {        		
        parent::__construct($options, [8]);
		$this->db = new MySql();
    }

    function run() {

		if(!isset($_SESSION['userID'])){
			echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
		}

		$dataGrid = new DataGrid();


		$sql = "SELECT * from t_interface_list where 1 ";
		$result = $dataGrid->create($this->options, $sql);//获取数据

		if($result['data']){
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['il_id'];

				if($v['il_reviewed'] == 1){
					$result['data'][$k]['il_reviewed'] = '<span class="font-green">审核通过</span>';
				}
				if($v['il_reviewed'] == 2){
					$result['data'][$k]['il_reviewed'] = '<span class="font-blue">待审核</span>';
				}

				$result['data'][$k]['il_ic_id'] = $this->db->getField("select ic_name from t_interface_category where ic_id = '".$v['il_ic_id']."'");

				$result['data'][$k]['op'] = '
					<a href="/work/editInterFace/?_ajax=1&id='. $v['il_id'] .'" data-target="#edit_interface" data-toggle="modal" class="btn-xs blue"><i class="fa fa-edit"></i> 修改编辑</a>
					<a href="/work/request/?_ajax=1&id='. $v['il_id'] .'" data-target="#request-modal" data-toggle="modal" class="btn-xs blue"><i class="fa fa-cog"></i> 请求参数</a>
					<a href="/work/returns/?_ajax=1&id='. $v['il_id'] .'" data-target="#return-modal" data-toggle="modal" class="btn-xs blue"><i class="fa fa-cog"></i> 返回参数</a>
					<a href="javascript:void(0);" onclick="javascript:complete('.$v["il_id"].');"><i class="fa fa-trash"></i> 移除</a>';
			}
		}
		echo json_encode($result);
    }

}
