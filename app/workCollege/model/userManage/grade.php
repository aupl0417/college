<?php

class grade extends worker {
	function __construct($options) {		
        parent::__construct($options, [60113]);
    }

    function run() {	
		$options = $this->options;

        $id = isset($options['id'])?$options['id']: "";
		if($id == "" ){
			$this->show(message::getJsonMsgStruct('1002', 'id错误'));//ID错误
			exit;
		}
		$db = new MySql();
		$gplp = $db->getRow("select * from t_company_gplp where cgl_uid='".$id."'");
		if ($gplp){
		    $this->show(message::getJsonMsgStruct('1002', '该用户是代理用户'));//ID错误
		    exit;
		}

		$res = $db->getRow("select * from t_user where u_id = '".$id."'");
		if(!isset($res)){
			$this->show(message::getJsonMsgStruct('1002','参数错误'));
			exit;
		}

		if($res['u_type'] == 1){  //企业会员判断是否是联盟商家
			$isUnionSeller = $db->getRow("select u.u_id,uc.u_isUnionSeller from t_user as u left join t_user_company as uc on u.u_id = uc.u_id where u.u_id='".$id."'");
			if ($isUnionSeller){
				if ($isUnionSeller['u_isUnionSeller'] == 1){
					$this->show(message::getJsonMsgStruct('1002', '该用户是联盟商家,降级失败！'));//ID错误
					exit;
				}
			}else{
				$this->show(message::getJsonMsgStruct('1002', '参数错误'));//ID错误
				exit;
			}
		}

		$data = array(
		    'u_lowergrade'     => 1,
		    'u_level'          => 1,
		    'u_lowergradeTime' => F::mytime(),
		);
		$result = $db->update('t_user',$data, "u_id = '".$id."'");
		
		//记录操作日志
		$data['memo'] = '会员降级';
		$data['old_level'] = $res['u_level'];
		log::writeLogMongo(60113, 't_user', $id, $data);
		
		if($result){
            $this->show(message::getJsonMsgStruct('1001', "降级成功！"));//成功	
		}else{
		    $this->show(message::getJsonMsgStruct('1002', "降级失败！"));//失败
		}
	}
}
