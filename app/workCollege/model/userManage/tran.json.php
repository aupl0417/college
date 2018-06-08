<?php

class tran_json extends worker {
	function __construct($options) {
		$type = isset($options['type']) ? ($options['type'] - 0) : 0;
		$powers = ['601', '60101', '60102', '60103', '60104', '60105', '60106'];//60102--60106  冻结 解冻 修改推荐人  密码重置  等级调整
        parent::__construct($options, [$powers[$type]]);
    }

    function run() {
		if(!isset($this->options['id'])){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;			
		}
		if(!isset($this->options['type'])){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;			
		}
        $user = new user();
		$userInfo = $user->getUserByID($this->options['id'], 'u_level, u_fCode, u_code,u_email,u_tel');

		$userInfo['telEmail'] = 0;
		if(F::isNotNull($userInfo['u_tel']) && F::isPhone($userInfo['u_tel'])){
			$userInfo['telEmail'] = 0;
		}elseif(F::isNotNull($userInfo['u_email']) && F::isEmail($userInfo['u_email'])){
			$userInfo['telEmail'] = 1;
		}else{
			$userInfo['telEmail'] = 2;
		}

		$info = array(
			'level' => $userInfo['u_level'],
			'fCode' => $userInfo['u_fCode'],
			'code'  => $userInfo['u_code'],
			'telEmail' => $userInfo['telEmail'],
		);
		//echo "select u_nick from t_user where u_code='".$info['fCode']."'";
		if($info['fCode'] != ''){
			$db = new MySql();
			$info['fName'] = $db->getField("select u_nick from t_user where u_code='".$info['fCode']."'");			
		}
		else{
			$info['fName'] = '无';
		}
		
		$this->show(message::getJsonMsgStruct('1001', $info));
		exit;
	}
}
