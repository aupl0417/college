<?php

class login_json extends guest {

    function run() {
		$user = new user();
		$cache = new cache();
		$ip = md5(F::GetIP());
        if ($cache->get($ip) == 1){
            if (!$user->uniqueUserInfo(5, $this->options['code'], '', 'code')) {
                $this->show(message::getJsonMsgStruct('2001','验证码错误')); //验证码错误
                exit;
            }
        }
		$username = $this->options['username'];
		$password = F::getSuperMD5($this->options['password']);
		$arr = ['username'=>$username,'password'=>$password,'iswap'=>1];

		$result = apis::request('/college/api/getUser.json', ['userName' => $username], true);
		
		$res = apis::request('u/api/publicUserLogin.json', $arr,true); //调用登录接口
		if($res){
			if(isset($res['code']) && $res['code'] == '1001'){
				//保存会员用户信息,用来处理直接跳转到大唐天下登陆使用
				foreach($res['data'] as $k=>$v){
					$_SESSION[$k] = $v;
				}
				$callbak['url'] = U('/u/index/index');  //跳转的url
				if($res['data']['userClass'] == 0 ){
					$certNum = $user->getPersonByID($res['data']['userID'],'u_certNum');
					if(!isset($certNum['u_certNum']) || empty($certNum['u_certNum'])){
						$callbak['url'] = U('/u/profile/personPerfect', '', 1);  //跳转的url
					}
				}
				$this->show(message::getJsonMsgStruct('2100', $callbak)); //登录成功
			}else{
				$cache->set($ip,'1',30);
				$this->show(message::getJsonMsgStruct('2199', $res['data'])); //登录失败
			}
		}else{
			$this->show(message::getJsonMsgStruct('1002')); //操作失败
		}
    }
}
