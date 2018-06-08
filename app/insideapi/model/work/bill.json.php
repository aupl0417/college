<?php

class bill_json extends guest {
    function __construct($options) {        		
        parent::__construct($options, [8]);
    }

    function run() {
		$dataGrid = new DataGrid();


		$sql = "SELECT * from b_bill AS b LEFT JOIN t_user AS u ON b.b_user_id = u.u_id where 1 ";
		$result = $dataGrid->create($this->options, $sql);

		if($result['data']){
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['b_id'];


				if($result['data'][$k]['b_stute']==1){
					$result['data'][$k]['b_stute'] = '未支付';
				}
				if($result['data'][$k]['b_stute']==2){
					$result['data'][$k]['b_stute'] = '已经支付';
				}

				$result['data'][$k]['op'] = '<a href="/open/billInfo/?_ajax=1&id='. $v['b_id'] .'" data-target="#billInfo" data-toggle="modal" class="btn-xs blue"><i class="fa fa-search"></i> 查看详情</a>';
			}
		}
		echo json_encode($result);
    }

}
