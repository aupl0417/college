<?php

/**
 * 用户基类
 * @author flybug
 * @version 2.0.0
 */
class user {

	protected $db;
    protected $mgdb;
	protected $userID = ''; //编号
	protected $userNick = ''; //昵称
	protected $userLogo = ''; //照片头像
	protected $userLevel = ''; //用户等级
	protected $userDepartment = ''; //雇员所属部门
	protected $userType = ''; //类型 0-雇员;1-会员
    protected $userClass = '';//用户类型 0、个人 1、企业
	protected $userPwdMd5 = ''; //密码（MD5字符串加密）
	protected $userPower = ''; //权限
	protected $userState = ''; //用户当前状态
	protected $userFcode = ''; //邀请注册人的推广码
	protected $userCode = ''; //推广码
	protected $userPhone = ''; //手机
	//protected $userCertNum = ''; //身份证
	protected $userCreateTime = ''; //注册时间
    protected $iswap = '';//用户登录设备 1、PC 2、APP 3、PC第三方登录 4、APP第三方登录

	//构造函数

	function __construct($db = NULL) {
		$this->db = is_null($db) ? new MySql() : $db;
		if (isset($_SESSION['userID']) && isset($_SESSION['userNick'])) {
			$this->userID = $_SESSION['userID'];
			$this->userNick = $_SESSION['userNick'];
			$this->userLogo = $_SESSION['userLogo'];
			$this->userLevel = $_SESSION['userLevel'];
			$this->userDepartment = $_SESSION['userDepartment'];
			$this->userType = $_SESSION['userType'];
			$this->userPwdMd5 = $_SESSION['userPwdMd5'];
			$this->userPower = $_SESSION['userPower'];
			$this->userState = $_SESSION['userState'];
			$this->userFcode = $_SESSION['userFcode'];
			$this->userCode = $_SESSION['userCode'];
			$this->userPhone = $_SESSION['userPhone'];
            $this->userClass = $_SESSION['userClass'];
		}
	}

    /**
     * 设置初始化对象
     * @param $id
     * @return int
     */
	function setSession($id){
		$cache = new cache();
		$userCache = $cache->get($id);
		if($userCache){
			$this->userID = $userCache['userID'];
			$this->userNick = $userCache['userNick'];
			$this->userLogo = $userCache['userLogo'];
			$this->userLevel = $userCache['userLevel'];
			$this->userDepartment = $userCache['userDepartment'];
			$this->userType = $userCache['userType'];
			$this->userPwdMd5 = $userCache['userPwdMd5'];
			$this->userPower = $userCache['userPower'];
			$this->userState = $userCache['userState'];
			$this->userFcode = $userCache['userFcode'];
			$this->userCode = $userCache['userCode'];
			$this->userPhone = $userCache['userPhone'];
            $this->userClass = $userCache['userClass'];
            $this->iswap  = $userCache['iswap'];
        }else{
			return -2016;
		}
		
	}

	//获得新的用户id
	function getNewUserID() {
		return F::getGID(32);
	}

	//获得新的u_code
	function getNewUserCode() {
		$maxUserCode = $this->db->getField('select max(u_code) from t_user');
		$maxUserCode = (F::isNotNull($maxUserCode)) ? $maxUserCode : 0;
		$maxUserCode = $maxUserCode + mt_rand(10, 40);
		return $maxUserCode + date('s', time());
	}

    /**
     * 注册用户
     * @param $vartab
     * @param $data
     * @return number|string
     */
	function add($vartab, $data) {
		$data['u_id'] = $this->getNewUserID();
		$data['u_code'] = $this->getNewUserCode();
		$data['u_createTime'] = F::mytime(); //注册时间
		$data['u_upgradeTime'] = F::mytime(); //升级时间
		$group = new group();
		$data['u_groupId'] = $group->getMemberDefault();
		$data['u_powerList'] = ''; //默认权限
		$data['u_powerHash'] = F::powerHash($data['u_powerList']); //权限校验hash
		$result = $this->db->insert($vartab, $data);
		if($result != 1){
			return false;
		}
		return $data['u_id'];
       
	}
	
	//登陆奖励 //byy

    /**
     * 注册奖励的验证是否赠送 -- bayayun
     * @param $userCreateTime 用户创建时间
     * @param $userState 用户状态
     * @return array|bool
     */
    function registerScoreCheck($userCreateTime,$userState){
        if(empty($userCreateTime)){
            return false;//没有参数
        }
       /*获取活动时间*/
        $register_award_start_time =  attrib::getSystemParaByKey('register_award_start_time');
        $register_award_end_time =  attrib::getSystemParaByKey('register_award_end_time');
        if(!F::verifyDateTime($register_award_start_time) || !F::verifyDateTime($register_award_end_time)){
            return false;//如果开始时间和结束时间不是有效的时间,那么退出
        }
        if($userCreateTime < $register_award_start_time || $userCreateTime > $register_award_end_time){
            return false;//注册时间不在奖励时间段中
        }
        if($userState < 1){
            return false;//注册注册用户不属于正常用户
        }
        /* 注册送白积分 */
        $register_white_score = attrib::getSystemParaByKey('register_white_score');
        $register_white_score_desc = attrib::getSystemParaByKey('register_white_score_desc');
        if($register_white_score > 0){
            $regAry = array(
                'register_award_start_time'=>$register_award_start_time,
                'register_award_end_time'=>$register_award_end_time,
                'register_white_score'=>$register_white_score,
                'register_white_score_desc'=>$register_white_score_desc,
            );
        }
        return !empty($regAry) ? $regAry : false;
    }

    /**
     * 保存用户状态 -- bayayun
     * @param $login 1、PC 2、接口
     * 如果登陆来源是接口,那么存入memcache
     */
	function saveUser($login=1) {
		if ($this->userID != '') {
            if($login == 1){
                $_SESSION['userID'] = $this->userID;
                $_SESSION['userNick'] = $this->userNick;
                $_SESSION['userLogo'] = $this->userLogo;
                $_SESSION['userLevel'] = $this->userLevel;
                $_SESSION['userDepartment'] = $this->userDepartment;
                $_SESSION['userType'] = $this->userType;
                $_SESSION['userPwdMd5'] = $this->userPwdMd5;
                $_SESSION['userPower'] = $this->userPower;
                $_SESSION['userState'] = $this->userState;
                $_SESSION['userFcode'] = $this->userFcode;
                $_SESSION['userCode'] = $this->userCode;
                $_SESSION['userPhone'] = $this->userPhone;
                $_SESSION['userClass'] = $this->userClass;
            }else{//存入memcache
                $cache = new cache();
                $cacheId = $this->userID;
                $cacheData = array(
					'userID' => $this->userID,
					'userNick' => $this->userNick,
					'userLogo' => $this->userLogo,
					'userLevel' => $this->userLevel,
					'userDepartment' => $this->userDepartment,
					'userType' => $this->userType,
					'userPwdMd5' => $this->userPwdMd5,
					'userPower' => $this->userPower,
					'userState' => $this->userState,
					'userFcode' => $this->userFcode,
					'userCode' => $this->userCode,
					'userPhone' => $this->userPhone,
					'userClass' => $this->userClass,
                );

                $cache->set($cacheId,$cacheData,604800);//缓存一个星期
            }

		}
	}

    /**
     * 结束用户的会话状态 -- bayayun
     * 如果是来源是接口需传递用户id,那么要清空memcache
     * @param string $id 用户id
     */
	static function exitUser($id = '') {
        if($id){
            /*清楚memcache缓存数据*/
            $cache = new cache();
            $userCache = $cache->get($id);
            if($userCache){
                $cache->del($id);
            }
        }
        $_SESSION = array(); //清除session全局变量
        if (isset($_COOKIE[session_name()])) {//清除客户端cookie.
            setcookie(session_name(), '', Date(time()) - 60, '/', DOMAIN);
        }
        setcookie(session_name(), '');
        session_destroy(); //销毁session.

	}

	//根据用户id,查询t_user得到用户信息，包括账户信息。
	function getUserByID($id, $fields = '*') {
		$sql = "SELECT $fields FROM t_user WHERE u_id = '$id' LIMIT 1";
		return $this->db->getRow($sql);
	}
    
    //根据用户id,查询t_user_person得到个人用户信息，包括账户信息
    function getPersonByID($id, $fields = '*') {
		$sql = "SELECT $fields FROM t_user_person WHERE u_id = '$id' LIMIT 1";
		return $this->db->getRow($sql);
	}
    
    //根据用户id,查询t_user_company得到企业用户信息，包括账户信息
    function getCompanyByID($id, $fields = '*') {
		$sql = "SELECT $fields FROM t_user_company WHERE u_id = '$id' LIMIT 1";
		return $this->db->getRow($sql);
	}
    
    //根据用户id,查询联表信息(t_user和t_user_person)
    function getUserPersonByID($id, $fields = '*') {
		$sql = "SELECT
                	$fields
                FROM
                	t_user
                LEFT JOIN t_user_person  ON t_user.u_id = t_user_person.u_id
                WHERE
                	t_user.u_id = '$id'
                LIMIT 1";
        $result = $this->db->getRow($sql);
        if($result){
            $result['u_id'] = $id;
        }
		return $result;
	}
    
    //根据用户id,查询联表信息(t_user和t_user_company)
    function getUserCompanyByID($id, $fields = '*') {
        $sql = "SELECT
            $fields
            FROM
            t_user
            LEFT JOIN t_user_company  ON t_user.u_id = t_user_company.u_id
            WHERE
            t_user.u_id = '$id'
            LIMIT 1";
        $result = $this->db->getRow($sql);
        if($result){
            $result['u_id'] = $id;
        }
        return $result;
    }

    /**
     * 根据用户id得到用户信息，等级信息,验证信息,账户信息(暂无)。 -- bayayun
     * @param $id
     * @param string $fields
     * @param bool $getAuth   
     * @return array|bool
     */
    function getFullUserInfo($id, $fields = '*',$getAuth = true) {
        /*先获取用户类型*/
        $userInfo = $this->getUserByID($id, 'u_type,u_level');
        if(!$userInfo){
            return false;
        }
        $type = $userInfo['u_type'];
        $level = $userInfo['u_level'];
        /*根据类型获取不同的数据*/
        if($type == 1){ //企业用户
            $userInfo = $this->getUserCompanyByID($id, $fields);
			$userInfo['userTypeName'] = '企业用户';
        }else{
            $userInfo = $this->getUserPersonByID($id, $fields);
			$userInfo['userTypeName'] = '个人用户';
        }

		if($userInfo){
            $userAuth = new userAuth();
            $userInfo['levelName'] = $this->db->getField("select ul_name from t_user_level where ul_id='".$level."'");
            $userInfo['userAuthInfo'] = $getAuth ? $userAuth->getAuthInfo($userInfo['u_id'],$userInfo['u_type']) : array();
            return $userInfo;
        }else{
        	return false;
        }
    }





	//处理tfs图片路径
	function imgpath($imgArray, $idx){
		$len = count($imgArray);
		if($len > $idx){//如果数组中存在图片地址
			if(strpos($imgArray[$idx], '/home/wwwroot') !== false || $imgArray[$idx] == ''){//如果是tfs上传失败保存在本地的//WEBROOT
				//return str_replace(WEBROOT, '', $imgArray[$idx]);
				return '';
			}
			elseif(strpos($imgArray[$idx], DOMAIN) !== false){
				return $imgArray[$idx];
			}
			else{//tfs正常			
				return TFS_APIURL .'/'. $imgArray[$idx];
			}
		}else{
			return '';
		}
	}


	//测试是否登录()
	function testLoginState($act, $power) {
		//是否登陆
		
		if (!isset($_SESSION['userID']) && !isset($_SESSION['userNick'])) {
			return -1;
		}

		//未激活用户
		if (isset($_SESSION['userID'])) {// && !isset($_SESSION['userNick'])
			if(isset($_SESSION['userState']) && $_SESSION['userState'] == 2){//临时用户,完善资料
				return -4;
			}else{//输入密码登录账户
				//return -5;
			}

		}

		//已登陆，校验访问身份
		if (!in_array($_SESSION['userType'], $act)) {
			return -2;
		}
		//return 1;
		//已登陆，校验访问者权限
		if (count($power)) {
			$userpower = explode(',', $this->userPower);
			foreach ($power as $v) {
				if (!in_array($v, $userpower)) {
					return -3;
				}
			}
		}
		
		return 1;
	}

	//临时用户激活
	function activeUser($userName, $userPwd, $certNum, $mobile){

		$user = $this->db->getRow("select * from t_user where u_tel='".$mobile."'");

		if(!$user){//用户不存在
			return -2;
		}

		if($user['u_state'] != 2){//不是未激活账号
			return -3;
		}
		if(!$this->uniqueUserInfo(1, $userName, $user['u_id'])){
			return -4;
		};

		if(!$this->uniqueUserInfo(4, $certNum, $user['u_id'])){
			return -5;
		};

		$password = F::getSuperMD5($userPwd);
		$data = [
			'u_nick'	 => $userName,
			'u_loginPwd' => $password,
			'u_certNum'	 => $certNum,
			'u_state'	 => 1,
			'u_type'	 => 0
		];

		$result = $this->db->update('t_user', $data, "u_id = '".$user['u_id']."'");
		if(!$result){
			return -6;
		}
		return $this->checkUser($userName, $userPwd);
	}
	
	//校验功能权限
	function checkPower($opid) {
		return in_array($opid, explode(',', $this->userPower));
	}

    /**
     * 平台账号登录（内部登录可用账号为昵称、手机、邮箱）
     * @param $name 用户登录名称
     * @param $pwd  用户登录密码
     * @param $iswap 用户登录设备 1、PC 2、APP 3、PC第三方登录 4、APP第三方登录
     * @return string
     */
    function checkUser($name,$pwd,$iswap=1) {
		$useTel = false;
		$useEmail = false;
		//根据不同内部账号自动判断
		if (F::regularCheck(2,$name) && is_numeric($name)) {
			$field = 'u_tel';
			$useTel = true;
		} elseif (F::regularCheck(3,$name)) {
			$field = 'u_email';
			$useEmail = true;
		} else {
			$field = 'u_nick';
		}
        /*查询登录用户*/
		$sql = "SELECT * FROM t_user WHERE $field = '$name'";
		$row = $this->db->getRow($sql);
        /*判断登录用户是否存在*/
		if (!$row) {
			/* 用户名是否手机号码或者email */
			if($useTel || $useEmail){
				$sql = "SELECT count(u_nick) FROM t_user WHERE u_nick = '$name'";
				$result = $this->db->getField($sql);
				if($result > 0){
					$_SESSION['resetUser'] = $name;
					return '2109';//如果存在,返回用户名称不符合规范
				}
			}
			return '2102'; //帐号不存在
		}
		/*判断用户状态*/
		if ($row['u_state'] == -1) {
			return '2103'; //停止
		} elseif ($row['u_state'] == 0) {
			return '0003'; //冻结
		}
		if ($row['u_isQuit'] == 1) {
			return '2106'; //已退会
		}
		if($row['u_logout'] == 1){
			return '2107'; //已注销
		}
        /*判断用户密码是否正确*/
        if($pwd != $row['u_loginPwd']){
            return '2105'; //密码错
        }
		/*检测用户名是否是平台限制用户*/
/* 		if (!F::regularCheck(1,$row['u_nick'])) {
				$dttxUsers = ['adadsa'];//内部帐号可不重置
				if(!in_array($row['u_nick'], $dttxUsers)){
                    $_SESSION['resetUser'] = $row['u_nick'];
                    return '2109';//如果不匹配,返回用户名称不符合规范
				}

		} */
		$userClass = $row['u_type']; //用户分类
		$userType = 1; //用户身份,能在这里登录的都是会员身份,雇员无法在这里登录
        
		/*
         * 权限的复合校验：
         * 权限校验的逻辑是，先取出用户自己的权限（t_user表ul_powerList字段），再取出等级表中用户对应等级
         * 的权限（t_user_level表ul_powerList），最后取出用户组表中的对应组权限（t_group表g_powerList），三个
         * 权限拼接后作为用户的最终权限
         */
		//校验用户权限
		$userPowerList = '';//权限
		$userPowerHash = '';//加密
		if ($row['u_powerList'] != 'all') {

			$userPowerList = $row['u_powerList']; //用户本身权限
			$userPowerHash = $row['u_powerHash'];

			if (F::isNotNull($userPowerList) && !F::checkPowerHash($userPowerList, $userPowerHash)) {
				//return '2113'; //权限字符串被篡改
			}
			//获取所属分类的权限列表
			$level = [1,3,4];//会员等级
			if (in_array($row['u_level'],$level)) {
				$sql = 'select ul_powerList,ul_powerHash from t_user_level where ul_id = ' . $row['u_level'];
				$ret = $this->db->getRow($sql);
				if (F::checkPowerHash($ret['ul_powerList'], $ret['ul_powerHash'])) {
					$userPowerList .= ',' . $ret['ul_powerList']; //加上级别权限
				}
			}

			//获取所属分类的权限列表
			if ($row['u_groupId'] > 0) {
				$groupPowerList = group::getGroupPower(1);
				$userPowerList .= ',' . $groupPowerList; //加上组权限
			}
		} else {
			$userPowerList = '';
			$sql = 'select p_id from t_power';
			$ret = $this->db->getAll($sql);
			foreach ($ret as $v) {
				$userPowerList .= ',' . $v['p_id'];
			}
			$userPowerList = $userPowerList . ',';
		}
		
		//等级权限//暂无
		//去掉权限中的重复值和空值
		$powerArray = explode(',', $userPowerList);
		$powerArray = array_unique($powerArray);
		$key = array_search('', $powerArray);
		if (F::isNotNull($key)) {
			unset($powerArray[$key]);
		}
		$userPowerList = implode(',', $powerArray);
        /*正常登录--设置数据对象*/
        $this->userID = $row['u_id'];
        $this->userNick = $row['u_nick'];
        $this->userLogo = $row['u_logo'];
        $this->userLevel = $row['u_level'];
        $this->userDepartment = 0;//会员没有部门
        $this->userPwdMd5 = $row['u_loginPwd'];
        $this->userType = 1; //保存身份标志
        $this->userClass = $userClass; //保存身份标志
        $this->userPower = $userPowerList;
        $this->userState = $row['u_state'];
        $this->userCode = $row['u_code']; //用户推广码
        $this->userFcode = $row['u_fCode']; //注册邀请人id
        $this->userPhone = $row['u_tel']; //用户手机
        $this->userCreateTime = $row['u_createTime']; //注册时间
        $this->iswap = $iswap;
        /*登录方式PC*/
        if($iswap == 1 || $iswap == 3){
            $this->saveUser(); //将查询结果储存到$_SESSION
        }elseif($iswap == 2 || $iswap == 4){/*登录方式APP*/
            $this->saveUser(2);
        }
        $time = F::mytime();
        $sql = "UPDATE t_user SET u_logCount=u_logCount+1, u_logTime = '$time',u_logIp = '" . F::GetIP() . "' WHERE u_id = '{$row['u_id']}'";
        $this->db->exec($sql);
        return '2100'; //成功

	}

	//平台账号登录（内部登录可用账号为昵称、手机、邮箱）
	function checkEmployee($name, $pwd) {
		//过滤非安全字符
		//  if (!preg_match('/^ylh\d{5}$/', $name)) {
		//     return '2110'; //员工账号格式不对
		//  }

		$sql = "SELECT * FROM t_employee WHERE e_id = '$name'";
		$row = $this->db->getRow($sql);

		if (!$row) {
			return '2102'; //帐号不存在
		}
		if ($row['e_state'] == -1) {
			return '2103'; //离职
		} elseif ($row['e_state'] == 0) {
			return '0003'; //冻结
		}
//echo $pwd .'                      --                '. $row['e_loginPwd'];
		if ($pwd != $row['e_loginPwd']) {
			return '2105'; //密码错
		}
		$userType = 2; //用户身份,能在这里登录的都是会员身份,雇员无法在这里登录


		/*
         * 权限的复合校验：
         * 权限校验的逻辑是，先取出用户自己的权限（t_user表ul_powerList字段），再取出等级表中用户对应等级
         * 的权限（t_user_level表ul_powerList），最后取出用户组表中的对应组权限（t_group表g_powerList），三个
         * 权限拼接后作为用户的最终权限
         */
		//校验用户权限
		if ($row['e_powerList'] != 'all') {

			$userPowerList = $row['e_powerList']; //用户本身权限
			$userPowerHash = $row['e_powerHash'];
			if (!F::checkPowerHash($userPowerList, $userPowerHash)) {
				return '2113'; //权限字符串被篡改
			}

			//获取所属部门的权限列表
		/*	$sql = 'select dm_powerList,dm_powerHash from t_organization where dm_id = ' . $row['e_departmentID'];
			$ret = $this->db->getRow($sql);
			if($ret){
				if (F::checkPowerHash($ret['dm_powerList'], $ret['dm_powerHash'])) {
					$userPowerList .= ',' . $ret['dm_powerList']; //加上级别权限
				}
			}*/

		} else {
			$userPowerList = '';
			$sql = 'select p_id from t_power_work';
			$ret = $this->db->getAll($sql);
			foreach ($ret as $v) {
				$userPowerList .= ',' . $v['p_id'];
			}
			$userPowerList = $userPowerList . ',';
		}
		//echo $userPowerList;die;
		//log::writeLogMongo(12235, '', 1, $userPowerList);
		//等级权限//暂无
		//去掉权限中的重复值和空值
		$powerArray = explode(',', $userPowerList);
		$powerArray = array_unique($powerArray);
		$key = array_search('', $powerArray);
		if (F::isNotNull($key)) {
			unset($powerArray[$key]);
		}
		$userPowerList = implode(',', $powerArray);
		//正常登录
		$this->userID = $row['e_id'];
		$this->userNick = $row['e_id'];
		$this->userLogo = $row['e_photo'];
		$this->userLevel = 0;//雇员没有会员等级
		$this->userDepartment = $row['e_departmentID'];
		$this->userPwdMd5 = $row['e_loginPwd'];
		$this->userType = $userType; //保存身份标志
		$this->userPower = $userPowerList;
		$this->userState = $row['e_state'];

		$this->userCode = ''; //用户推广码
		$this->userFcode = ''; //注册邀请人id
		$this->userPhone = ''; //用户手机
		$time = F::mytime();
		$sql = "UPDATE t_employee SET e_logCount = e_logCount + 1, e_logTime = '$time',e_logIp = '" . F::GetIP() . "' WHERE e_id = '{$this->userID}'";
		$this->db->exec($sql);
		$this->saveUser(); //将查询结果储存到$_SESSION/memcache
		return '2100'; //成功
	}

	function uniqueUserInfo($type, $val, $id = '', $code = 'checkcode') {
		if($id !='' ){//检测指定用户id
			$where = " and u_id <> '" . $id . "'";
		}else{
			$where = isset($_SESSION['userID']) ? " and u_id <> '" . $_SESSION['userID'] . "'" : '';
		}
		
		switch ($type) {
			case 1://用户名
				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user` where u_nick='" . $val . "'" . $where);
					return ($v == 0);
				} else {
					return false;
				}
				break;
			case 2://手机
				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user` where u_tel='" . $val . "'" . $where);
					return ($v == 0);
				} else {
					return false;
				}
				break;
			case 3://推荐人
				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user` where u_code='" . $val . "'" . $where);
					return ($v > 0);
				} else {
					return false;
				}
				break;
			case 4://身份证
				//if (!idcard::idcard_checksum18($val)) {
				//return false;
				//}
				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user_person` where u_certNum='" . $val . "'" . $where);
					return ($v == 0);
				} else {
					return false;
				}
				break;
			case 5://验证码
				if (F::isNotNull($val)) {
					$validate = new validate();
					$validate->type = $code;
					return $validate->validate($val, false);
				} else {
					return false;
				}
				break;
			case 6://公司名称
				/* 				if(!preg_match('/^0?(13[0-9]|15[012356789]|17[0678]|18[0-9]|14[57])[0-9]{8}$/', $val)){
                  return false;
                  } */

				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user_company` where u_companyName='" . $val . "'" . $where);
					return ($v == 0);
				} else {
					return false;
				}
				break;
			case 7://营业执照
				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user_company` where u_comLicenseCode='" . $val . "'" . $where);
					return ($v == 0);
				} else {
					return false;
				}
				break;
			case 8://邮箱
				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user` where u_email='" . $val . "'".$where);
					return ($v == 0);
				} else {
					return false;
				}
				break;
			case 9://组织机构代码证编号
				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user_company` where u_comOrgCode='" . $val . "'" . $where);
					return ($v == 0);
				} else {
					return false;
				}
				break;
			case 10://税务登记证编号
				if (F::isNotNull($val)) {
					$v = $this->db->getField("select count(1) from `t_user_company` where u_comTaxCode='" . $val . "'" . $where);
					return ($v == 0);
				} else {
					return false;
				}
				break;
			default:
				return false;
		}
	}

	function setUserParent($id, $level = 0, $up = true){
		
		$result = $this->db->getRow("select u_nick, u_root, u_level,u_fCode from t_user where u_id = '".$id."'");
		
		if($level){//有传新的等级过来
			$result['u_level'] = $level;
		}
		
		//log::writeLogMongo(12499, $result['u_fCode'] ,$result);
		if($result){
			$parentLevel = $this->db->getField("select u_level from t_user where u_code='".$result['u_fCode']."'");
			if(!$parentLevel){
				return false;
			}
			if($parentLevel >= $result['u_level']){//推荐人等级符合条件
				return $result['u_fCode'];
			}
			$result['u_root'] = str_replace(',0,', ',0', $result['u_root']);	
/* 			if($id == 'f99d1fb942b7a95bd286fdcd39b7a3a2'){
				
			}else{
				$parent = $this->db->getRow("select u_root, u_code from t_user where u_code in (".$result['u_root'].") and u_level>=".$result['u_level']." order by u_lnum desc limit 1");////0.
			} */
			$parent = $this->getRightParent($result['u_fCode'], $result['u_level']);
			if($parent){
				$fcode = $parent['u_code'];
				if($fcode == '0'){
					$root = '0';
				}else{
					$root = $fcode.','.$parent['u_root'];
				}

			}else{
				$root = '0';
				$fcode = 0;
			}
			$update = [
				'u_root'  => $root,
				'u_fCode' => $fcode
			];
			if($up){
				if($this->db->update('t_user', $update, "u_id='".$id."'")){
					log::writeLogMongo(88880, 'upgrade', $result['u_nick'], ['old'=>$result['u_fCode'], 'new'=>$update['u_fCode']]);
					return $update['u_fCode'];
				}else{
					return false;
				};
			}else{
				return $update;
			}
		}else{
			return false;
		}
	}
	
	function getRightParent($code, $level){
		$sql = "select u_root, u_code, u_level, u_fCode, u_lnum, u_id from t_user where u_code='".$code."'";
		$parent = $this->db->getRow($sql);
		if(!$parent){
			return false;
		}else{
			if($parent['u_level'] >= $level){
				return $parent;
			}else{
				return self::getRightParent($parent['u_fCode'], $level);
			}
		}
	}
	
	//检查用户升级的提成是否发放
	function checkUpgradeCommission($id){
		//先检查该用户是否金钻及以上
		$userInfo = $this->getUserByID($id);
		
		if($userInfo['u_level'] < 3){
			return -101;
		}
		
		//检查用户升级是否在新系统上有资金流转记录
		$sql = "SELECT * FROM `t_account_cash_tran` WHERE ca_businessId=205 AND ca_from = '".$id."'";
		$result = $this->db->getRow($sql);
		if(!$result){//如果没有记录,那么不是在新系统上升级的
			return -102;
		}
		
		//如果有记录,但是没有相应的订单
		if(F::isEmpty($result['ca_orderId'])){
			return -103;
		}
		
		//如果有订单记录,那么检查关联的订单号码有无奖励发放记录
		$sql = "SELECT * FROM `t_account_cash_tran` WHERE ca_businessId=220 AND ca_orderId = '".$result['ca_orderId']."'";
		$result = $this->db->getRow($sql);
		if($result){//如果有奖励记录,那么已发放
			return 1;
		}else{
			return -104;
		}		
		
	}
	
	//提成发放的方法
	function upgradeCommission($id){
		
		//先检查该用户是否金钻及以上//取出用户信息
		$userInfo = $this->getFullUserInfo($id);
		
		if($userInfo['u_level'] < 3){
			return -101;
		}
		
		//检查用户升级是否在新系统上有资金流转记录
		$sql = "SELECT * FROM `t_account_cash_tran` WHERE ca_businessId=205 AND ca_from = '".$id."'";
		$upgradeInfo = $this->db->getRow($sql);
		if(!$upgradeInfo){//如果没有记录,那么不是在新系统上升级的
			return -102;
		}
		
		$o_id = '';
		//如果有记录,但是没有相应的订单
		if(F::isNotNull($upgradeInfo['ca_orderId'])){			
			$o_id = $upgradeInfo['ca_orderId'];
		}else{
		
			//账户异动,扣掉现金账户,同时返还白积分
			$o_id = F::getTimeMarkID(); //订单编号
			//插入订单表			
			$data = array(
				'bu_id'				=>$o_id,
				'bu_type'			=>205,
				'bu_money'			=>$upgradeInfo['ca_money'],
				'bu_buyUid'			=>$id,
				'bu_sellUid'		=>ADMIN_ID,
				'bu_createTime'		=>F::mytime(),
				'bu_returnPercent'	=>1,
				'bu_state'			=>5,
				'bu_memo'			=>'预存款支付用户升级'
			);
			$result = $this->db->insert('t_order', $data);
			//log::writeLogMongo(88881, 'upgrade', $userInfo['u_nick'], 2);
			if($result != 1){
				log::writeLogMongo(88881, 'upgrade', $userInfo['u_nick'], 3);
				return -103;
			}
			$update = ['ca_orderId' => $o_id];
			$result = $this->db->update('t_account_cash_tran', $update, " ca_id='".$upgradeInfo['ca_id']."'");
			//echo $result;
			if($result != 1){
				//log::writeLogMongo(88881, 'upgrade', $userInfo['u_nick'], 4);
				return -103;
			}
		}
		
		$account = new account($this->db);
		//是否发放白积分给用户
		$sql = "SELECT count(1) FROM `t_account_score_tran` WHERE sc_to='".$id."' AND sc_businessId=106 AND sc_orderId = '".$o_id."'";
//echo $sql;
		$result = $this->db->getField($sql);
	
		if($result == 1){//已经返利白积分
			
		}else{
			/* 返还白积分 */
			if (!$account->transferScore('106', ADMIN_ID, 5, $id, 5, F::bankerAlgorithm($upgradeInfo['ca_money'], 100, 2), '', 0, 2, $o_id, '升级到'.$userInfo['levelName'])){
				return -104;
			}
			
		}
		//log::writeLogMongo(88881, 'upgrade', $userInfo['u_nick'], 7);
		//print_r($userInfo);return -1031;
		$hasMis = false;//是否发放佣金
		$hasReward = false;//是否发放提成嘉奖
		//如果有订单记录,那么检查关联的订单号码有无佣金发放记录
		$sql = "SELECT count(*) FROM `t_account_cash_tran` WHERE ca_businessId=220 AND ca_orderId = '".$o_id."'";
		$result = $this->db->getField($sql);
		if($result > 0){//如果有佣金记录,那么已发放佣金
			//log::writeLogMongo(88881, 'upgrade', $userInfo['u_nick'], 6);
			$hasMis = true;
			//提成嘉奖
			$sql = "SELECT count(*) FROM `t_account_cash_tran` WHERE ca_businessId=111 AND ca_orderId = '".$o_id."'";
			$result = $this->db->getField($sql);
			if($result > 0){//如果有提成嘉奖记录,那么已发放提成嘉奖				
				$hasReward = true;
				return true;
			}else{
				//return $o_id;//返回订单id去计算提成嘉奖
			}
		}else{
			//log::writeLogMongo(88881, 'upgrade', $userInfo['u_nick'], 8);
			//取出用户推荐人信息
			$sql = "select u_id, u_level from t_user where u_code='".$userInfo['u_fCode']."'";
			$parentInfo = $this->db->getRow($sql);
			if(!$parentInfo){//推荐人不存在
				return -105;
			}
			
			//推荐人等级不符合条件
			if($parentInfo['u_level'] < $userInfo['u_level']){
				return -106;
			}
			
			if($hasMis === false){
			//给推荐用户发放提成（佣金）
				/* 奖励金额 */
				if($userInfo['u_level'] == 3){	//金钻奖励20
					$money = 20;
				}else if($userInfo['u_level'] == 4){ //铂族奖励200
					$money = 200;
				}

				//给推荐人佣金账户奖励
				
				if($parentInfo['u_id'] != ADMIN_ID){	//推荐人是admin，不用嘉奖
					//判断有无发放推荐奖励,如果没有,那么要补发
					$sql = "SELECT count(1) FROM `t_account_cash_tran` WHERE ca_to='".$parentInfo['u_id']."' AND ca_businessId=220 AND ca_orderId = '".$o_id."'";
					$result = $this->db->getField($sql);
					if(!$result){//如果没有,那么要补发
						if (!$account->transferCash('220', ADMIN_ID,3,$parentInfo['u_id'], 3, $money, '', 0, 2, $o_id, $userInfo['u_nick'].'升级到'.$userInfo['levelName']."，推荐奖励 ".$money."元佣金")){
							log::writeLogMongo(88881, 'upgrade', $id, 9);
							return $account->getError();
						}
					}
					//判断有无扣10%税费,如果没有,那么要补扣
					//扣10%税费
					$sql = "SELECT count(1) FROM `t_account_cash_tran` WHERE ca_to='".$parentInfo['u_id']."' AND ca_businessId=230 AND ca_orderId = '".$o_id."'";
					$result = $this->db->getField($sql);
					if(!$result){//如果没有,那么要补发
						if (!$account->transferCash('230', $parentInfo['u_id'],3,$parentInfo['u_id'], 4, F::bankerAlgorithm($money, 0.1), '', 0, 2, $o_id, $userInfo['u_nick'].'升级到'.$userInfo['levelName']."，推荐奖励 ".$money."元佣金扣除10%税费")){
						
							return $account->getError();
						}
					}
				}
			}
		
			return $o_id;//返回订单id去计算提成嘉奖
		}
		
	}

	//设置用户cookie，所有平台统一
	static function setCookieCrossDomain($type = 1) {
		$des = new STD3Des(PASSKEYWORD);
		$id = base64_encode($des->encrypt(session_id()));
		foreach (SYS_WEBTABLE as $v) {
			if ($type) {
				echo '<script src="http://' . $v . '/?model=login&do=setcookie&c=' . $id . '"></script>';
			} else {
				echo '<script src="http://' . $v . '/?model=login&do=setcookie&a=clear"></script>';
			}
		}
	}
	// 拼接修改密码参数，排序返回
	static function get_arr_format_str($arr){
	    $res = '';
	    if(is_array($arr)){
	        krsort($arr);
	        $str = implode($arr, '_');
	        $F = new F();
	        $res = $F::getSuperMD5($str);
	    }

	    return $res;
	}
	//返回邮箱登录地址
	static function return_email_address($email){
		$reg = '/@\S*$/';
		$res = preg_match($reg,$email,$resArr);
		if($res){
			$emailArr = array(
				'@gmail.com'=>'https://accounts.google.com/',
				'@139.com'=>'http://mail.10086.cn/',
				'@outlook.com'=>'https://login.live.com/',
				'@hotmail.com'=>'https://login.live.com/',
			);
			if(array_key_exists($resArr[0],$emailArr)){
				return $emailArr[$resArr[0]];
			}
		}
		return 'http://mail.'.substr($resArr[0],1);
	}

    /**
     * 通过推荐码查询推荐码的上级推荐码--bayayun
     * @param $code
     * @return array
     */
    public function getFcodeThree($code){
        /*获取推荐用户的推广码和用户ID已经推荐人推荐码*/
        $sql = "SELECT u_id,u_fCode FROM t_user WHERE u_code = '".$code."'";
        $res = $this->db->getRow($sql);
        $info = array();
        if($res){
            if($res['u_fCode'] > -1){//不是admin
                /*获取二级推荐人信息*/
                $data = $this->getUidByCode($res['u_fCode']);
                if($data){
                    if($data['u_fCode'] > -1){
                        /*获取三级推荐人信息*/
                        $into = $this->getUidByCode($data['u_fCode']);
                    }else{//二级推荐人是admin,那么三级推荐人也是admin
                        $into = $data;
                    }
                }
			}else{//推荐人是admin,那么二级推荐人和三级推荐人都是admin
				$data = $res;
				$into = $res;
			}
            $info['one']['u_id']= $res['u_id'];
            $info['one']['u_code']= $code;
            $info['two']['u_id']= !empty($data['u_id']) ? $data['u_id'] : '';
            $info['two']['u_code']= !empty($data['u_code']) ? $data['u_code'] : '';
            $info['three']['u_id']= !empty($into['u_id']) ? $into['u_id'] : '';
            $info['three']['u_code']= !empty($into['u_code']) ? $into['u_code'] : '';
        }
        return !empty($info) ? $info : array();

    }

    /**
     * 通过推荐码查询推荐码的用户信息--bayayun
     * @param $code
     * @return array
     */
    public function getUidByCode($code){
        $sql = "SELECT u_id,u_code,u_fCode,u_nick FROM t_user WHERE u_code = '".$code."' LIMIT 1";
        $res = $this->db->getRow($sql);
        return $res;
    }

    /**
     * 通过用户ID更新会员总数--bayayun
     * @param $uid
     * @param $uid2
     * @param $uid3
     */
    public function UpdateUcnumByUid($uid,$uid2,$uid3){
        $str = false;
        if(!empty($uid)){
            $sql = "UPDATE t_user SET u_cNum = u_cNum + 1 WHERE u_id = '".$uid."'";
            $up = $this->db->exec($sql);
            if($up){
                $str = true;
            }
        }
        if(!empty($uid2)){
            $sqlb = "UPDATE t_user SET u_cNum2 = u_cNum2 + 1 WHERE u_id = '".$uid2."'";
            $upt = $this->db->exec($sqlb);
            if($upt){
                $str = true;
            }
        }
        if(!empty($uid3)){
            $sqlc = "UPDATE t_user SET u_cNum3 = u_cNum3 + 1 WHERE u_id = '".$uid3."'";
            $upd = $this->db->exec($sqlc);
            if($upd){
                $str = true;
            }
        }
        return $str;

    }

	//返回会员ID
	public function getUserID(){
		return $this->userID;
    }

    //获取u_config,unserialize后的值
    public function getUserConfig($keyName=''){
        $uid = $this->userID;
        $sql = "SELECT u_config FROM t_user WHERE u_id='$uid'";
        $uConfig = $this->db->getField($sql);
        $uConfig = unserialize($uConfig);
        if (!empty($keyName)) {
            return $uConfig[$keyName];
        }

        return $uConfig;
    }

    /* --------------------------------------------------------------------------*/
    /**
        * @添加虚拟用户
        * @Param $code  推荐码
        * @Returns   bool
        * author wuyuanhang
     */
    /* ----------------------------------------------------------------------------*/
    public function createVirtualUser($code){
        if (empty($code)) {
            return false;
        }
        $group = new group();
        $insertData = array(
            'u_id'          => $this->getNewUserID(),
            'u_nick'        => strtotime("now"),
            'u_code'        => $code,
            'u_createTime'  => F::mytime(),
            'u_upgradeTime' => F::mytime(),
            'u_groupId'     => $group->getMemberDefault(),
            'u_powerList'   => '',
            'u_powerHash'   => F::powerHash(''),
        );
        $result = $this->db->insert('t_user', $insertData);
        return $result < 1 ? false : true;
    }
}
?>
