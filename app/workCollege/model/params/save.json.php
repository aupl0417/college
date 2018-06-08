<?php

class save_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [401]);
    }

    function run() {	
		$options = $this->options;
		//print_r($options);die;
		$id = isset($options['id']) ? (F::fmtNum($options['id']) - 0) : 0;//id
		$all = isset($options['all']) ? (F::fmtNum($options['all']) - 0) : 0;//是否修改整行
		$times = isset($options['times']) ? (F::fmtNum($options['times']) - 0) : 0;//倍数
		$name = isset($options['name']) ? $options['name'] : '';//字段
		$value = isset($options['value']) ? $options['value'] : '';//值
		$source = isset($options['source']) ? $options['source'] : '';//表
		$join = isset($options['join']) ? $options['join'] : 0;//是否多个拼接为一个值
		$idx = isset($options['idx']) ? (F::fmtNum($options['idx']) - 0) : 0;//拼接的序号
		$tables = array(
			'crr' => array(
				'table' => 't_company_reward_rule',
				'id'	=> 'ri_id',
				'fields'=> ['ri_L1', 'ri_L2', 'ri_L3', 'ri_L4', 'ri_L5', 'ri_L6', 'ri_L7']
			),
			'cs' => array(
				'table' => 't_company_struct',
				'id'	=> 'cs_level'
			),
			'cp' => array(
				'table' => 't_company_product',
				'id'	=> 'cp_id'
			),
			'rt' => array(
				'table' => 't_reward_type',
				'id'	=> 'rt_id'
			),
			'ra' => array(
				'table' => 't_reward_awards',
				'id'	=> 'ra_id'
			)
		);
		
		//echo $id;echo $source;die;
		if($id <= 0 || $source == '' || !array_key_exists($source, $tables)){//
			$this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误	
			exit;
		}
		$table = $tables[$source]['table'];		
		$update = array();
		if($times){
			$value = F::bankerAlgorithm($value, $times, 4);
		}
		$db = new MySql();	
		/* 拼接参数 */
		if($join == 1){
			/* 先取出数据库中的值,拼接后为新的值 */
			$sql = "select `".$name."` from `".$table."` where `".$tables[$source]['id']."` = '".$id."'";
			$val = $db->getField($sql);
			$spArray = explode('|', $val);
			for($k = 0;$k<=$idx;$k++){
				if(!array_key_exists($k, $spArray)){
					$spArray[$k] = '';
				}
			}
			$spArray[$idx] = $value;
			$value = implode('|', $spArray);
		}
		
		if($all && array_key_exists('fields', $tables[$source])){
			$fields = $tables[$source]['fields'];
			$update = array_fill_keys($fields, $value);
		}else{
			$update[$name] = $value;
		}
		
		$where = " `".$tables[$source]['id']."` = '".$id."'";
		//print_r($update);print_r($table);print_r($where);die;
			
		$result = $db->update($table, $update, $where);
		//记录操作日志
		$update['memo'] = '业务参数修改';//尽量写得详细一点点了
		log::writeLogMongo(401, $table, $id, $update);
		if($result){			
			$this->show(message::getJsonMsgStruct('1001', $result));//成功
		}
		else{
			$this->show(message::getJsonMsgStruct('1002', $where));//失败
		}		
	}
}
