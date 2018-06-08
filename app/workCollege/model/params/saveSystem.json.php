<?php

class saveSystem_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [404]);
    }

    function run() {	
		$options = $this->options;
		//print_r($options);die;
		$id = isset($options['id']) ? (F::fmtNum($options['id']) - 0) : 0;//id
		
		$name = isset($options['name']) ? $options['name'] : '';//字段
		$value = isset($options['value']) ? $options['value'] : '';//值
		
		//echo $id;echo $source;die;
		if($id <= 0){//
			$this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误	
			exit;
		}
		
		$db = new MySql();	

		
		$where = " `sys_id` = '".$id."'";
		$update[$name] = $value;
		//print_r($update);print_r($table);print_r($where);die;
			
		$result = $db->update('t_system', $update, $where);
		//记录操作日志
		$update['memo'] = '系统参数修改';
		log::writeLogMongo(404, 't_system', $options['id'], $update);
		
		/* 更新memcache缓存 */
		attrib::getSystemParaByKey('');
		if($result){			
			$this->show(message::getJsonMsgStruct('1001', $result));//成功
		}
		else{
			$this->show(message::getJsonMsgStruct('1002', $where));//失败
		}		
	}
}
