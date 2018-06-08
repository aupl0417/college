<?php
/* 获取学员信息接口
 * @param $userId   type : string 用户id   userId,userName,mobile不能同时为空
 * @param $userName   type : string 用户会员名称   userId,userName,mobile不能同时为空
 * @param $mobile   type : string 用户手机   userId,userName,mobile不能同时为空
 * @param $sync type : int    是否同步ERP信息      默认 ：1 同步       如果唐人大学中没有该用户,则从erp中导入该用户,如果唐人大学中有该用户则同步会员等级和认证状态
 * @author adadsa
 * */
 
 /**
 * @api                    {post} /api/getUser.json 获取学员信息接口
 * @apiDescription         获取学员信息,并同步erp中用户信息
 * @apiName                getUser.json
 * @apiGroup               学员
 * @apiPermission          adadsa 
 *
 * @apiParam {string}     userId      用户id		userId,userName,mobile不能同时为空
   @apiParam {string}     userName    用户会员名称		userId,userName,mobile不能同时为空
   @apiParam {string}     mobile       用户手机   	userId,userName,mobile不能同时为空
   @apiParam {int}        sync      是否同步ERP信息      默认 ：1 同步       如果唐人大学中没有该用户,则从erp中导入该用户,如果唐人大学中有该用户则同步会员等级和认证状态
 *
 *
 * @apiSuccessExample      Success-Response:
	 *	{
	 code: "1001",
	 data: {
	 userId: "289",
	 nick: "ceshi2",
	 name: "",
	 id: "2c086588d81e9ec09ae4b6743e83d0cb",
	 certNum: null,
	 level: "3",
	 auth: "1111",
	 avatar: "https://image.dttx.com/v1/tfs/T15VKTByKT1RCvBVdK.jpg"
	 }
	 }
 *
 */
class getUser_json extends api{
	
 
    private $db;
	private $sdk;
	private $userInfo;
	
    function run() {
		$this->db = new MySql();
		$this->sdk  = new openSdk();
		
        //验证参数是否存在
		$userId = isset($this->options['userId']) ? trim($this->options['userId']) : '';
		$userName = isset($this->options['userName']) ? trim($this->options['userName']) : '';
		$mobile = isset($this->options['mobile']) ? trim($this->options['mobile']) : '';
		if($userId == '' && $userName == '' && $mobile == ''){
			return apis::apiCallback('1002','参数错误!');
		}
		
		$sync = isset($this->options['sync']) ? F::fmtNum($this->options['sync']) : 1;
		$sync = (in_array($sync, [0 ,1])) ? $sync : 1;
        
        $payType = (isset($this->data['payType']) && !empty($this->data['payType'])) ? $this->data['payType'] + 0 : 0;
		
		$whereSql = '';
		$erpParam = '';
		/* u_id检索 */
		if($userId != ''){
			$whereSql = "userId = '" . $userId . "'";
			$erpParam = $userId;
		}
		/* u_nick检索 */
		if($userName != '' && $whereSql == ''){
			$whereSql = "username = '" . $userName . "'";
			$erpParam = $userName;			
		}
		/* u_tel检索 */
		if($mobile != '' && $whereSql == ''){
			$whereSql = "mobile = '" . $mobile . "'";
			$erpParam = $mobile;			
		}
		
		$userFields = "id as userId, username as nick,mobile as tel,email,type,trueName as name, userId as id, certNum,level,auth,avatar,authImage,mobile,code,identityType";
		$userInfo = $this->db->getRow('select '. $userFields .' from tang_ucenter_member where ' . $whereSql);

		if($sync == 0){//如果不同步erp信息,那么在这里就返回数据
			return apis::apiCallback('1001', $userInfo);
		}
		
		if(empty($userInfo)){//如果唐人大学中没有该用户
			/* 导入erp用户信息 */
			$erpUserInfo = $this->getUser($erpParam);
			
			if(!$erpUserInfo){//如果ERP中也没有该用户,那么抛出错误,不再继续执行
				return apis::apiCallback('1002','用户信息不存在!');
			}
			else{//如果ERP中有该用户,那么判断该用户是否是创客以上并且通过实名认证,如果符合条件导入该用户信息到唐人大学
				/* if(substr($erpUserInfo['auth'], 2, 1) != '1' && $erpUserInfo['level'] < 3){
					return apis::apiCallback('1002','该会员未通过实名认证!该会员不是创客或创投会员!');
				};
				
				if(substr($erpUserInfo['auth'], 2, 1) != '1'){
					return apis::apiCallback('1002','该会员未通过实名认证!');
				};
				
				if($erpUserInfo['level'] < 3){
					return apis::apiCallback('1002','该会员不是创客或创投会员!');
				}; */
				
				$uid = $this->addUser($erpUserInfo);
				if($uid){//写入唐人大学用户成功,重新读取该用户信息
					$userInfo = $this->db->getRow('select '. $userFields .' from tang_ucenter_member where id="' . $uid . '"');
				}
				else{//写入唐人大学用户失败
					return apis::apiCallback('1002','系统错误,请联系系统管理员!');
				};
			}			
		}
		else{//如果唐人大学中有该用户,那么更新用户等级及认证信息			
			/* 同步erp用户信息 */
			$erpUserInfo = $this->getUser($userInfo['id']);
			
			if($erpUserInfo){
			    if($erpUserInfo['type'] == 1){
			        if(!empty($erpUserInfo['comLeadName']) && !empty($erpUserInfo['leadCardNum'])){
			            $erpUserInfo['certNum'] = $erpUserInfo['leadCardNum'];
			        }else if(!empty($erpUserInfo['comLegalName']) && !empty($erpUserInfo['legalCardNum'])) {
			            $erpUserInfo['certNum'] = $erpUserInfo['legalCardNum'];
			        }
			    }else if($erpUserInfo['type'] == 0) {
			        $erpUserInfo['certNum'] = $erpUserInfo['certNum'];
			    }
			    
			    $erpUserInfo['name'] = $erpUserInfo['type'] == 1 ? !empty($erpUserInfo['comLegalName']) ? $erpUserInfo['comLegalName'] : $erpUserInfo['comLeadName'] : $erpUserInfo['name'];
			    $collegeInfo = [$userInfo['code'],$userInfo['certNum'],$userInfo['type'], $userInfo['avatar'], $userInfo['level'], $userInfo['auth'], $userInfo['nick'], $userInfo['tel'], $userInfo['email'], $userInfo['name']];
			    $erpInfo = [$erpUserInfo['code'],$erpUserInfo['certNum'],$erpUserInfo['type'], $erpUserInfo['avatar'], $erpUserInfo['level'], substr($erpUserInfo['auth'], 0, 4), $erpUserInfo['nick'], $erpUserInfo['tel'], $erpUserInfo['email'], $erpUserInfo['name']];
			    
			    if($collegeInfo != $erpInfo){//如果唐人大学保存的主要资料和erp中不一样,那么更新
			        $where = "id='".$userInfo['userId']."'";
			        $update = [
			            'username'            => $erpUserInfo['nick'],
			            'trueName'            => $erpUserInfo['type'] == 1 ? !empty($erpUserInfo['comLegalName']) ? $erpUserInfo['comLegalName'] : $erpUserInfo['comLeadName'] : $erpUserInfo['name'],
			            'avatar'              => $erpUserInfo['avatar'],
			            'email'               => $erpUserInfo['email'],
			            'userId'              => $erpUserInfo['id'],
			            'certNum'             => $erpUserInfo['certNum'],
			            'mobile'              => $erpUserInfo['tel'],
			            'auth'                => substr($erpUserInfo['auth'], 0, 4),
			            'code'                => $erpUserInfo['code'],
			            'type'                => $erpUserInfo['type'],
			            'level'               => $erpUserInfo['level'],
			            'update_time'         => time(),
			        ];
			        
			        if($this->db->update('tang_ucenter_member', $update, $where) != 1){
			            return apis::apiCallback('1002','系统错误,请联系系统管理员!');
			        }
			        else{
			            $userInfo = $this->db->getRow('select '. $userFields .' from tang_ucenter_member where id="' . $userInfo['userId'] . '"');
			        };
			    }
			}
		}
		
		return apis::apiCallback('1001', $userInfo);
    }
	
    private function getUser($userId){
        $params['input'] = $userId;
        $path = '/user/getUser';
        $result = $this->sdk->request($params, $path);
        
        if(!is_array($result))  return false;
        if($result['id'] != 'SUCCESS' && $result['id'] != 'SUCCESS_EMPTY')  return false;
        
        $userInfo = $result['info'];
        if(!$userInfo) return false;
        
        return $userInfo;
    }
    
    private function addUser($userInfo, $branchId = 0){
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
			'auth'                => strlen($userInfo['auth']) > 4 ? substr($userInfo['auth'], 0, 4) : $userInfo['auth'],
			'certType'            => $userInfo['certType'],
			'type'                => $userInfo['type'],
			'level'               => $userInfo['level'],
            'code'                => $userInfo['code'],
			'reg_time'            => $time,
			'reg_ip'              => F::GetIP(),
			'last_login_time'     => $time,
			'last_login_ip'       => F::GetIP(),
			'update_time'         => $time,
        );
		
        if(isset($userInfo['au_authImg']) && !empty($userInfo['au_authImg'])){
            $userData['authImage'] = serialize($userInfo['au_authImg']);
        }
		
        if($userInfo['type'] == 1){
            if(!empty($userInfo['comLeadName']) && !empty($userInfo['leadCardNum'])){
                $userData['certNum'] = $userInfo['leadCardNum'];
			}
			else if(!empty($userInfo['comLegalName']) && !empty($userInfo['legalCardNum'])) {
                $userData['certNum'] = $userInfo['legalCardNum'];
            }
            $userData['trueName'] = !empty($userInfo['comLegalName']) ? $userInfo['comLegalName'] : $userInfo['comLeadName'];
		}
		else if($userInfo['type'] == 0) {
            $userData['certNum'] = $userInfo['certNum'];
        }
        
        $res = $this->db->insert('tang_ucenter_member', $userData);
        if(!$res){
            return false;
        }
		
        return $this->db->getLastID();
    }	
    
}
