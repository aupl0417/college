<?php

class logout extends worker {
	function __construct($options) {		
        parent::__construct($options, [60114]);
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

		$type = $db->getField("select u_type from t_user where u_id = '".$id."'");
		if(!isset($type)){
			$this->show(message::getJsonMsgStruct('1002','参数错误'));
		}

		if($type == 1){ //企业用户
			$userInfo = $db->getRow("select * from t_user as u left join t_user_company as uc on u.u_id = uc.u_id where u.u_id='".$id."'");
			if ($userInfo){
				if ($userInfo['u_isUnionSeller'] == 1){
					$this->show(message::getJsonMsgStruct('1002', '该用户是联盟商家,注销失败！'));//ID错误
					exit;
				}
			}else{
				$this->show(message::getJsonMsgStruct('1002', '参数错误'));//ID错误
				exit;
			}
		}else{//个人用户
			$userInfo = $db->getRow("select * from t_user where u_id='".$id."'");
			if(!$userInfo){
				$this->show(message::getJsonMsgStruct('1002','参数错误'));
				exit;
			}
		}

		$data = array(
		    'u_logout'    => 1,
		    'u_state'     => 0,
		    'u_loginPwd'  => "",
		    'u_level'     => 1,
		    'u_tel'       => "",
		    'u_email'     => "",
		    'u_logoutTime'=> F::mytime(),
		);
		$dataPer = array(
			'u_certNum' => "",
		);
		$dataCom = array(
			'u_comLicenseCode' =>"",
			'u_companyName'=>"",
			'u_comOrgCode'=>"",
			'u_comTaxCode'=>"",
		);

		try{
		    $db->beginTRAN();

			if($type == 0){
				$res = $db->update('t_user_person',$dataPer, " u_id = '".$id."'");
			}else{
				$res = $db->update('t_user_company',$dataCom, " u_id = '".$id."'");
			}
			if(!$res){
				throw new Exception('1');
			}

    		$result = $db->update('t_user',$data, "u_id = '".$id."'");
    		if($result){
				//记录操作日志
				$data['memo'] = '注销会员操作';
				log::writeLogMongo(60114, 't_user', $id, $userInfo);
				
				$db->commitTRAN();
				$this->show(message::getJsonMsgStruct('1001', "注销成功！"));//成功 
				exit;
    		    $accountInfo = $db->getRow("select * from t_account where ac_id='".$id."'");
    		    if ($accountInfo){
    		        //冻结账户
    		        $account = new account($db);

					
    		        if ($accountInfo['ac_freeMoney'] > 0 ){
        		        if(!$account->transferCash('401',$id,1, ADMIN_ID,1, $accountInfo['ac_freeMoney'], $_SESSION['userID'] ,0,2,"",'注销用户，清空该账号的现金账户')){
        		            throw new Exception('清空失败');
        		        }
    		        }
    		        if ($accountInfo['ac_busMoney'] >0){
        		        if(!$account->transferCash('401',$id,2, ADMIN_ID,2, $accountInfo['ac_busMoney'], $_SESSION['userID'] ,0,2, '','注销用户，清空该账号的交易账户')){
        		            throw new Exception('清空失败');
        		        }
    		        }
    		        if($accountInfo['ac_misMoney'] >0){
        		        if(!$account->transferCash('401',$id,3, ADMIN_ID,3, $accountInfo['ac_misMoney'], $_SESSION['userID'] ,0,2, '','注销用户，清空该账号的佣金账户')){
        		            throw new Exception('清空失败');
        		        }
    		        }
    		        if($accountInfo['ac_taxMoney'] >0){
    		            if(!$account->transferCash('401',$id,4, ADMIN_ID,4, $accountInfo['ac_taxMoney'], $_SESSION['userID'] ,0,2, '', '注销用户，清空该账号的税费')){
    		                throw new Exception('清空失败');
    		            }
    		        }
    		        if($accountInfo['ac_whiteScore'] >0){
        		        if(!$account->transferScore('401',$id,5, ADMIN_ID,5, $accountInfo['ac_whiteScore'], $_SESSION['userID'] ,0,2, '','注销用户，清空该账号的白积分')){
        		            throw new Exception('清空失败');
        		        }
    		        }
    		        if ($accountInfo['ac_redScore'] >0){
        		        if(!$account->transferScore('401',$id,6, ADMIN_ID,6, $accountInfo['ac_redScore'], $_SESSION['userID'] ,0,2, '','注销用户，清空该账号的红积分')){
        		            throw new Exception('清空失败');
        		        }
    		        }
    		        if($accountInfo['ac_storeScore'] >0){
        		        if(!$account->transferScore('401',$id,7, ADMIN_ID,7, $accountInfo['ac_storeScore'], $_SESSION['userID'] ,0,2, '','注销用户，清空该账号的库存积分')){
        		            throw new Exception('清空失败');
        		        }
    		        }
    		        if ($accountInfo['ac_lockedWhiteScore'] >0){
        		        if(!$account->transferScore('401',$id,8, ADMIN_ID,8, $accountInfo['ac_lockedWhiteScore'], 0 ,0,2, '','注销用户，清空该账号的冻结白积分')){
        		            throw new Exception('清空失败');
        		        }
    		        }

    		        $update = array(
    		            'ac_state'      => 0,
    		            'ac_memo'       =>'注销用户',
    		        );
    		        $logout = $db->update('t_account',$update, "ac_id = '".$id."'");
    		        if ($logout){
    		        	$changeLog = [
		                    'ut_uid'	  => $id,
		                    'ut_type'	  => 8,
		                    'ut_eid'	  => $_SESSION['userID'],
		                    'ut_ctime'	  => F::mytime(),
		                    'ut_oldValue' => 0,
		                    'ut_newValue' => 1,
		                    'ut_reason'   =>'用户注销操作',
		                ];
		                $change = $db->insert('t_user_tran', $changeLog);//写入用户表变动表
		                if ($change<1){
		                    throw new Exception('1');
		                }
    		            
    		            //记录操作日志
    		            $data['memo'] = '注销会员操作';
    		            log::writeLogMongo(60114, 't_user', $id, array_merge($userInfo, $accountInfo));
    		        
    		            $db->commitTRAN();
    		            $this->show(message::getJsonMsgStruct('1001', "注销成功！"));//成功
    		        }else{
    		            throw new Exception('1');
    		        }
    		    }else{
    		    	 $changeLog = [//写入用户表变动表
		                    'ut_uid'	  => $id,
		                    'ut_type'	  => 10,
		                    'ut_eid'	  => $_SESSION['userID'],
		                    'ut_ctime'	  => F::mytime(),
		                    'ut_oldValue' => 0,
		                    'ut_newValue' => 1,
		                    'ut_reason'   =>'用户注销操作',
		                ];
		                $change = $db->insert('t_user_tran', $changeLog);//写入用户表变动表
		                if ($change<1){
		                    throw new Exception('1');
		                }
    		        

    		    }
    		}else{
    		    throw new Exception('1');
    		}
		}catch(Exception $e){
		    $db->rollBackTRAN();
		    $this->show(message::getJsonMsgStruct('1002',"注销失败"));//注册失败
		}
	}
}
