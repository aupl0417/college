<?php

class saveTran_json extends worker {
    function __construct($options) {        	
		$type = isset($options['type']) ? ($options['type'] - 0) : 0;
		$powers = ['601', '60102', '60103', '60104', '60105', '60106'];//60102--60106  冻结 解冻 修改推荐人  密码重置  等级调整
        parent::__construct($options, [$powers[$type]]);		
    }

  function run() {
        $user = new user();
        $data = $this->options;
        $dataMsg = '';
        
        $id = isset($data['userid']) ? $data['userid'] : '';        
        if($id == ''){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;
        }
		
        $type = isset($data['type']) ? ($data['type'] - 0) : 0;        
        if($type == 0){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;
        }
		
        $reason = isset($data['reason']) ? $data['reason'] : ''; 
		
		$userInfo = $user->getUserByID($id);
        if ($type == 5){
    		if ($userInfo['u_lowergrade'] ==1 ){
    		    $this->show(message::getJsonMsgStruct('1002', '该用户降级过，不能调整等级'));//参数错误
    		    exit;
    		}
        }
		$changeLog = [//写入用户表变动表
			'ut_uid'	  => $id,
			'ut_type'	  => $type,
			'ut_eid'	  => $_SESSION['userID'],
			'ut_ctime'	  => F::mytime(),
			'ut_oldValue' => '',
			'ut_newValue' => '',
			'ut_reason'   =>'',
		];
		$update = [];//更新用户表	
		$account = [];//更新账户
		$db = new MySql();
		switch($type){//变动类型:1-冻结;2-解冻;3-修改推荐人;4-密码重置;5-等级调整;
			case 1://冻结
				if(empty($reason)){
					$this->show(message::getJsonMsgStruct('1002', '请填写冻结原因'));//参数错误
					exit;
				}

				$update['u_state'] = ($type - 1);
				break;
			case 2://解冻				   
				if(empty($reason)){
					$this->show(message::getJsonMsgStruct('1002', '请填写解冻原因'));//参数错误
					exit;
				}

				//如果帐号/账户是被admin冻结的，必须要用admin账号才能解冻，无视权限
				$mongo = new mgdb();
				$order = array('log_time'=>-1);  //倒序
				$lock = array(
					'log_type_id'	=> array('value' => 60102),
					'log_r_id'		=> array('value' => $id),
				);
				$lockInfo = $mongo->where($lock)->orderBy($order)->get('logs');

				if(isset($lockInfo) && ($lockInfo != array())){
					if(($lockInfo[0]['log_user'] == 'dttx00001') && ($_SESSION['userID'] != 'dttx00001')){
						$this->show(message::getJsonMsgStruct('1002','只有管理员才能解冻此账户！'));
						exit;
					}
				}

				$update['u_state'] = ($type - 1);
				break;
			case 3://修改推荐人	   
				if($reason == ''){
					$this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
					exit;
				}
				$fCode = isset($data['fCode']) ? ($data['fCode'] - 0) : 0;
				if ($fCode === 0 ){
				    $this->show(message::getJsonMsgStruct('1002', '不能修改成admin为推荐人'));//参数错误
				    exit;
				}
				
				$parent = $db->getRow("select u_fCode, u_root, u_level,u_lnum from t_user where u_code='".$fCode."' and u_id <> '".$id."'");
				if(!$parent){
					$this->show(message::getJsonMsgStruct('1002', '不是有效的推广码'));//不是有效的推广码
					exit;					
				}
				if($fCode >= $userInfo['u_code']){
					$this->show(message::getJsonMsgStruct('1002', '推荐人注册时间不能晚于被推荐人'));//推荐人注册时间不能晚于被推荐人
					exit;					
				}
				if($parent['u_level'] < $userInfo['u_level']){
					$this->show(message::getJsonMsgStruct('1002', '推荐人等级不能低于被推荐人'));//推荐人等级不能低于被推荐人
					exit;					
				}
				if($parent['u_fCode'] == $userInfo['u_code']){
					$this->show(message::getJsonMsgStruct('1002', '推荐人不能是被推荐人'));//不是有效的推广码
					exit;
				}
				$changeLog['ut_reason'] = $reason;
				$changeLog['ut_oldValue'] = $userInfo['u_fCode'];
				$changeLog['ut_newValue'] = $fCode;	
				if($fCode == '0'){
					$update['u_root'] = $fCode;
				}else{
					$update['u_root'] = $fCode.','.$parent['u_root'];
				}
				$update['u_lnum'] = $parent['u_lnum'] + 1;
				$update['u_fCode'] = $fCode;
				break;
			case 4://密码重置
				if(F::isNotNull($userInfo['u_tel']) && F::isPhone($userInfo['u_tel'])){
					$sms = new sms();
					$password = $sms->SendValidateSMS($userInfo['u_tel'],2);

					if($password){
						$update['u_loginPwd']	 = F::getSuperMD5($password);
						$dataMsg = '重置密码已经发送至客户的手机！';
					}else{
						$this->show(message::getJsonMsgStruct('1002', '发送消息失败'));
						exit;
					}
				}elseif(F::isNotNull($userInfo['u_email']) && F::isEmail($userInfo['u_email'])){
					put::sendmail();
					$mail = new letter();
					$password = $mail->sendCode($userInfo['u_email'],'云联商务大系统密码重置',2);
					if($password){
						$update['u_loginPwd'] = F::getSuperMD5($password);
						$dataMsg = '重置密码已经发送至客户的邮箱！';
					}else{
						$this->show(message::getJsonMsgStruct('1002', '发送消息失败'));
						exit;
					}
				}
				else{
					$this->show(message::getJsonMsgStruct('1002', '手机号或邮箱均不存在'));
					exit;
				}
				break;
			case 5://等级调整    
				$level = isset($data['level']) ? ($data['level'] - 0) : 0;        
				$changeLog['ut_reason'] = $reason;
				$changeLog['ut_oldValue'] = $userInfo['u_level'];
				$changeLog['ut_newValue'] = $level;
// 				dump($userInfo);die;
				$userlevel = $user->getRightParent($userInfo['u_fCode'],$level);
				/*返回参数*/
				$update['u_root'] = $userInfo['u_code'].",".$userlevel['u_root'];
				$update['u_fCode'] = $userlevel['u_code'];
				$update['u_lnum'] = $userlevel['u_lnum']+1;
				$update['u_level'] = $level;
				break;
			default:
				$this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
				exit;
		}
		
		if(!$update){
			$this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
			exit;
		}
		try{
			$db->beginTRAN();
			$update['u_lastUpdateTime'] = F::mytime();
			$db->update('t_user', $update, " u_id='".$id."'");//更新用户表
			
			//记录操作日志
			if ($type ==1){
    			$update['memo'] = '冻结用户';
    			$power = 60102;
			}else if ($type ==2){
			    $update['memo'] = '解冻用户';
			    $power = 60103;
			}else if ($type ==3){
			    $update['memo'] = '修改推荐人';
			    $power = 60104;
			}else if ($type ==4){
			    $update['memo'] = '密码重置';
			    $power = 60105;
			}else if ($type ==5){
			    $update['memo'] = '等级调整';
			    $power = 60106;
			}

			//写日志
			$state = array(
				0		=> '冻结',
				1		=> '正常',
				-1		=> '停止使用',
				2		=> '临时用户',
			);
			$memo = array(
				'memo'				=> $update['memo'],
				'被操作账号'		=> $userInfo['u_nick'],
				'被操作账号手机'	=> $userInfo['u_tel'],
				'被操作前账号状态'	=> $state[intval($userInfo['u_state'])],
				'操作原因'			=> $reason,
				'操作后账号状态'	=> $state[intval($update['u_state'])],
				'操作时间'			=> F::mytime(),
				'操作人'			=> $_SESSION['userID'],
			);
			log::writeLogMongo($power, 't_user', $id, $memo);
			
			$db->commitTRAN();
			$this->show(message::getJsonMsgStruct('1001','修改成功！'.$dataMsg));
		}
		catch(Exception $e){			
			$db->rollBackTRAN();
			$this->show(message::getJsonMsgStruct('1002', '错误'));//错误						
		}
  }
}
