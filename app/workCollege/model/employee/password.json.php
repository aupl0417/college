<?php

class password_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [500501]);
    }

    function run() {	
        $user = new user();
		$options = $this->options;
// 		dump($options);die;
        $id = isset($options['id'])?$options['id']: "";
		if($id == "" ){//
			$this->show(message::getJsonMsgStruct('1002', 'id错误'));//ID错误	
			exit;
		}
		$name = isset($options['name'])? $options['name']: "";
		if($name == "" ){//
		    $this->show(message::getJsonMsgStruct('1002', 'name错误'));//name错误
		    exit;
		}
		$phone = isset($options['phone'])? $options['phone']: "";
		if($phone == "" ){//
		    $this->show(message::getJsonMsgStruct('1002', '电话号码错误错误'));//phone错误
		    exit;
		}
    	if(!$user->uniqueUserInfo(2, $phone)){
			$this->show(message::getJsonMsgStruct('1002', '手机格式错误'));//手机错误
			exit;
		}	
		
		$duty = isset($options['duty'])? $options['duty']: "";
		if($duty == "" ){//
		    $this->show(message::getJsonMsgStruct('1002', 'duty错误'));//duty错误
		    exit;
		}
		$state = isset($options['state'])? $options['state']: "";
		if($state == "" ){//
		    $this->show(message::getJsonMsgStruct('1002', 'state错误'));//state错误
		    exit;
		}
		$department = isset($options['org'])? $options['org']: "";
		$db = new MySql();
		$dep = $db->getRow("select * from t_organization where dm_code = '".$department."'");
		$department = isset($dep['dm_id']) ? $dep["dm_id"] : "";
		if($department == "" ){//
		    $this->show(message::getJsonMsgStruct('1002', 'department错误'));//department错误
		    exit;
		}
		$update = [
			'e_name' 	      => $name,
		    'e_departmentID'  => $dep['dm_id'],
			'e_dutyID'	      => $duty,
			'e_state'         => $state,
		    'e_tel'           => $phone,
		];
		//print_r($update);
		$db = new MySql();		
		$result = $db->update('t_employee', $update, "e_id = '".$id."'");
		//记录操作日志
		$update['memo'] = '雇员密码修改';//尽量写得详细一点点了
		log::writeLogMongo(20204, 't_employee', $id, $update);
		if($result){			
			$this->show(message::getJsonMsgStruct('1001', '修改成功'));//失败
		}
		else{
			$this->show(message::getJsonMsgStruct('1002', '没有数据变动'));//失败
		}		
	}
}
