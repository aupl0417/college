<?php

class newBaseApi{
    
	protected $app = array(
	     '1' => 'D8OZLSE2NEDC0FR4XTGBKHY67UJZ8IK9', //ios
		 '2' =>'DFHGKZLSE2NFDEHGFHHR4XTGBKHY67EJZ8IK9', //安卓
	);
	protected $db;
	protected $data = array();
	protected $uid = null;
	protected $userInfo = array();
	protected $sdk;
	protected $isTest = 0;
	
    function __construct($options) {
        //验证设备类型ID是否存在或是否为空
		(!isset($options['appId']) || empty($options['appId'])) && $this->apiReturn(402);
		
		//验证签名串是否存在或是否为空
		(!isset($options['signValue']) || empty($options['signValue'])) && $this->apiReturn(403);
		
		$sign = $options['signValue'];
		unset($options['signValue']);
		$data = $this->paramsFiller($_REQUEST);
		unset($data['signValue']);

		foreach($data as $key=>&$val){
			if(in_array($key, array('content', 'title')) && substr_count($val, ' ') > 0){
				$val = str_replace(' ', '', $val);
			}
		}

		$this->data = $data;//子类继承用
		$this->validate = $this->signValidate($this->data, $sign);

		if(!$this->validate){
			$this->apiReturn(401, $this->newSign);//暂时显示这个签名，用于测试时
			// $this->apiReturn(401);
		}
		
		$this->db = new MySql();
		
		//如果存在userId，则判定该用户是否在当前数据库的用户表中存在，
		//如果存在，则将获取的id给$this->uid;
		$this->uid = '';
		$this->userInfo = array();
		
		//线上测试账号ID
		$userIds = array('133','645','144','110','189','1134','673','1146','1151','11622','86','159','117','84','202','13470','650','1479','114','8526','156');
		$this->isTest = 0;//不是测试账号
		if(isset($data['userId']) && !empty($data['userId'])){
		    
            $result = apis::request('/college/api/getUserInfo.json', ['userId' => $data['userId']], true);
            
		    // !is_array($result) && $this->apiReturn(1002, $result);
			if($result['code'] == '1001' && !empty($result['data'])){
				$this->userInfo = $result['data'];
				$this->uid = $this->userInfo['userId'];
				if(in_array($this->uid, $userIds)){
				    $this->isTest = 1;//是测试账号
				}
			}else{
				$res = apis::request('/college/api/getUser.json', ['userId' => $data['userId']], true);
				!is_array($res) && $this->apiReturn(1002, $res);
				if($res['code'] == '1001' && !empty($res['data'])){
					$this->userInfo = $res['data'];
					$this->uid = $this->userInfo['userId'];
					if(in_array($this->uid, $userIds)){
						$this->isTest = 1;//是测试账号
					}
				}
			}
		}
    }

	protected function paramsFiller($data){
		$paras = array();
		if(is_array($data)){
			foreach ($data as $k => $v) {
				if (is_array($v)) {
					$paras[$k] = $v;
				} else {
					$paras[$k] = packpara::updateValue($v);
				}
			}
		}
		return $paras;
	}
	
	//签名校验
	protected function signValidate($data, $sign){
		if(empty($data) || !is_array($data) || !($data['appId'] > 0) || !isset($this->app[$data['appId']])){
			return false;
		}
		
		$secretKey = $this->app[$data['appId']];
		ksort($data);
		$queryString = $this->http_build_string($data);
		
		if(md5("{$queryString}&{$secretKey}") != $sign){
			$this->newSign = md5( "{$queryString}&{$secretKey}");
			return false;
		}
		
		return true;
	}
	
	/**
	 * 跟系统的http_build_str()功能相同，但不用安装pecl_http扩展
	 *
	 * @param array $array      需要组合的数组
	 * @param string $separator 连接符
	 *
	 * @return string               连接后的字符串
	 * eg: 举例说明
	 */
	function http_build_string ( $array, $separator = '&' ) {
		$string = '';
		foreach ( $array as $key => $val ) {
			$string .= "{$key}={$val}{$separator}";
		}
		//去掉最后一个连接符
		return substr( $string, 0, strlen( $string ) - strlen( $separator ) );
	}

	/*
	* 	返回数据到客户端
	*	@param $code type : int		状态码
	*   @param $info type : string  状态信息
	*	@param $data type : mixed	要返回的数据
	*	return json
	*/
	function apiReturn($code, $info = '', $data = null){
		header('Content-Type:application/json; charset=utf-8');//返回JSON数据格式到客户端 包含状态信息
		include_once( WEBROOT . '/app/' . APP_NAME . '/language/message.php' );//引入状态信息数组 $msg

		$info = empty($info) ? (false === array_key_exists($code, $msg) ? '未定义异常信息' : $msg[$code]) : $info;

		$jsonData = array(
				'code' => $code,
				'msg'  => $info,
				'data' => $data ? $data : $info
		);

		exit(json_encode($jsonData));
	}
	
	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @return mixed
	 */
	function get_client_ip($type = 0) {
		$type       =  $type ? 1 : 0;
		static $ip  =   NULL;
		if ($ip !== NULL) return $ip[$type];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos    =   array_search('unknown',$arr);
			if(false !== $pos) unset($arr[$pos]);
			$ip     =   trim($arr[0]);
		}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip     =   $_SERVER['HTTP_CLIENT_IP'];
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf("%u",ip2long($ip));
		$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
	
	/**
     * 发送HTTP请求方法
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    protected function http($url, $params, $method = 'GET', $header = array(), $multi = false){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );
        
        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : $this->http_build_string($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            case 'GET':
            default:
                $opts[CURLOPT_URL] = $url . '?' . $this->http_build_string($params);
                break;
        }
    
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) return false;
        return  $data;
    }
    
    protected function getUser($userId){
        $this->sdk  = new openSdk();
        $params['input'] = $userId;
        $path = '/user/getUser';
        $result = $this->sdk->request($params, $path);
        
        !is_array($result) && $this->apiReturn(1002, $result);
        ($result['id'] != 'SUCCESS' && $result['id'] != 'SUCCESS_EMPTY') && $this->apiReturn(1002, $result['msg']);
        
        $userInfo = $result['info'];
        !$userInfo && $this->apiReturn(201);
        
        return $userInfo;
    }
    
    protected function addUser($userInfo, $branchId = 0){
        if(empty($userInfo)){
            return false;
        }
        $time = time();
        $userData = array(
            'username'            => $userInfo['nick'],
            'trueName'            => $userInfo['name'],
            'avatar'              => $userInfo['avatar'],
            'email'               => $userInfo['email'],
            'userId'              => $userInfo['id'],
            'tangCollege'         => $branchId,
            'mobile'              => $userInfo['tel'],
            'auth'                => $userInfo['auth'],
            'certType'            => $userInfo['certType'],
            'type'                => $userInfo['type'],
            'level'               => $userInfo['level'],
			'code'				  => $userInfo['code'],
            'reg_time'            => $time,
            'reg_ip'              => $this->get_client_ip(1),
            'last_login_time'     => $time,
            'last_login_ip'       => $this->get_client_ip(1),
            'update_time'         => $time,
        );
    
        if(isset($userInfo['au_authImg']) && !empty($userInfo['au_authImg'])){
            $userData['authImage'] = serialize($userInfo['au_authImg']);
        }
    
        if($userInfo['type'] == 1){
            if(!empty($userInfo['comLeadName']) && !empty($userInfo['leadCardNum'])){
                $userData['certNum'] = $userInfo['leadCardNum'];
            }else if(!empty($userInfo['comLegalName']) && !empty($userInfo['legalCardNum'])) {
                $userData['certNum'] = $userInfo['legalCardNum'];
            }
            $userData['trueName'] = !empty($userInfo['comLegalName']) ? $userInfo['comLegalName'] : $userInfo['comLeadName'];
        }else if($userInfo['type'] == 0) {
            $userData['certNum'] = $userInfo['certNum'];
        }
        
        $res = $this->db->insert('tang_ucenter_member', $userData);
        if(!$res){
            return false;
        }
    
        return $this->db->getLastID();
    }
}
