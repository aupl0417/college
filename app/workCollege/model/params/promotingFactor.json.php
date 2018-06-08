<?php

class promotingFactor_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [40701]);
    }

    function run() {
		$dataGrid = new DataGrid();
		
		$type = (isset($this->options['type']) && F::isNotNull($this->options['type'])) ? $this->options['type'] : 'factor_given';//默认取出赠送积分系数		

		$sql = "SELECT * FROM `t_promoting_factor` WHERE pf_type='".$type."'";

		$result = $dataGrid->create($this->options, $sql);//获取数据
		$now = time();
		$data = array();
		if($result['data']){			
			foreach($result['data'] as $k => $v){
				$result['data'][$k]['DT_RowId']		 = 'row_'.$v['pf_id'];//这一行是必须加上的,用于给table的tr加上标识;请使用主键或者unqiue字段
				if(strtotime($v['pf_time']) > $now){//如果当前参数未到生效时间,可以删除
					$result['data'][$k]['op'] = '<a href="javascript:void(0);" onclick="javascript:Params.delete(\''. $v['pf_id'] .'\');" class="btn-xs blue"><i class="fa fa-trash-o"></i> 删除</a>';		
				}else{
					$result['data'][$k]['op'] = ' ';
				}				
			}
		}
		echo json_encode($result);
	}
}