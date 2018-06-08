<?php

class setPwd extends worker {
	function __construct($options) {		
        parent::__construct($options, [500501]);
    }

    function run() {	
		$options = $this->options;
// 		dump($options);die;

        $id = isset($options['id'])?$options['id']: "";
		if($id == "" ){//
			$this->show(message::getJsonMsgStruct('1002', 'id错误'));//ID错误
			exit;
		}
		$where = "e_id = '".$id."'";
		$db = new MySql();
		
		$newPwd = substr(md5(F::getMtID()),0,6);
// 		dump($newPwd);die;s
		$data = array(
		    'e_loginPwd' => F::getSuperMD5($newPwd),
		);
		$result = $db->update('t_employee',$data, $where);
		
		//记录操作日志
		$data['memo'] = '雇员密码重置';//尽量写得详细一点点了
		log::writeLogMongo(20204, 't_employee', $options['id'], $data);
		
		if($result){
            $this->show(message::getJsonMsgStruct('1001', "重置密码成功！新密码为：".$newPwd));//成功	
		}else{
		    $this->show(message::getJsonMsgStruct('1002', "重置密码失败！"));//失败
		}
	}
}
