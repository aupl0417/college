<?php

class powerEmployee_json extends worker {
/* 	function __construct($options) {		
        parent::__construct($options, [20203]);
    } */

    function run() {	
		$options = $this->options;
		
		$id = isset($options['id']) ? $options['id'] : '';
// 		$level = isset($options['level']) ? (F::fmtNum($options['level']) - 0) : 0;
		$powerList = isset($options['power']) ? $options['power'] : '';
		//echo $id;echo $powerList;die;
		if($id == '' || $powerList == ''){//
			$this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误	
			exit;
		}

		$powerHash = F::powerHash($powerList);
		if($id != ''){//用户
			$table = 'tang_employee';
			$update = array(
				'e_powerList' => $powerList,
				'e_powerHash' => $powerHash,
			);
			$where = " e_id = '".$id."'";			
		}else{//等级
			$this->show(message::getJsonMsgStruct('1002', 'id错误'));//参数错误	
			exit;	
		}
		$db = new MySql();
		//dump($update);
		$result = $db->update($table, $update, $where);
		//记录操作日志
		$update['memo'] = '雇员权限修改';//
		//log::writeLogMongo(20203, 'tang_employee', $id, $update);
		if($result){			
			$this->show(message::getJsonMsgStruct('1001', $result));//成功
		}
		else{
			$this->show(message::getJsonMsgStruct('1002', $where));//失败
		}		
	}
}
