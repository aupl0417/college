<?php

class company_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60101]);			
    }

  function run() {
      
        $data = $this->options;
        $id = isset($data['userid']) ? $data['userid'] : '';
        if($id == ''){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;
        }
        $user = new user();
       
		$comLegalName = isset($data['comLegalName']) ? $data['comLegalName'] : '';       
        if($comLegalName==""){
            $this->show(message::getJsonMsgStruct('1002', '法人为空'));//法人为空
            exit;
        }
		$area = isset($data['area']) ? $data['area'] : '';     
        if($area==""){
            $this->show(message::getJsonMsgStruct('1002', '没有选择所在地区'));//没有选择所在地区
            exit;
        }
		$mobile = isset($data['mobile']) ? $data['mobile'] : '';
        if($mobile==""){
            $this->show(message::getJsonMsgStruct('1002', '没有写联系手机'));//没有写联系手机
            exit;
        }
          if(!$user->uniqueUserInfo(2,$mobile, $id)){
             $this->show(message::getJsonMsgStruct('1002', '该号码已经存在'));//手机错误
            exit;
        }
        
        
        $postage = isset($data['postage']) ? $data['postage']: "";
      
        $tel = isset($data['tel'])?$data['tel']:"";
/*         if($tel==""){
            $this->show(message::getJsonMsgStruct('1002', '没有写联系电话'));//没有写联系电话
            exit;
        } */
        $email = isset($data['email'])?$data['email']:"";
        if($email==""){
            $this->show(message::getJsonMsgStruct('1002', '没有写email'));//没有写email
            exit;
        }
        if(!$user->uniqueUserInfo(8,$email, $id)){
            $this->show(message::getJsonMsgStruct('1002', '该email已经存在'));//email错误
            exit;
        }

        $comAddress = isset($data['comAddress']) ? $data['comAddress'] : '';
        
        $qq = isset($data['qq'])?$data['qq']:"";
        
        
        $address = isset($data['address'])? $data['address'] : '';
        if($address==""){
            $this->show(message::getJsonMsgStruct('1002', '地址为空'));//地址为空
            exit;
        }

        $country = isset($data['country']) ? $data['country'] : '';
        if($country == ""){
            $this->show(message::getJsonMsgStruct('1002', '国家为空'));//国家为空
            exit;
        }
        if(!F::fmtNum($country)){
            $this->show(message::getJsonMsgStruct('1002', '国家格式错误'));//国家格式错误
            exit;
        }
        $companyType = isset($data['companyType']) ? $data['companyType'] : '';
        if($companyType == ''){
            $this->show(message::getJsonMsgStruct('1002','企业类型为空'));
            exit;
        }
		$indId = isset($data['indId']) ? $data['indId'] : 0;
		$mainIndustry = isset($data['mainIndustry']) ? $data['mainIndustry'] : '';
		$shopUrl = isset($data['shopUrl']) ? $data['shopUrl'] : '';
		$companyName = isset($data['companyName']) ? $data['companyName'] : '';
		$comArea = isset($data['comArea']) ? $data['comArea'] : 0;
		$comLeadName = isset($data['comLeadName']) ? $data['comLeadName'] : '';
		$isBranch = isset($data['isBranch']) ? $data['isBranch'] : 0;
		$companyThree = isset($data['companyThree']) ? $data['companyThree'] : 0;
        $arr = array(
			'u_area' => $area,
			'u_comAddress' => $comAddress,
			'u_address' => $address,
			'u_qq' => $qq,
			'u_otherTel'=>$tel,
			'u_postage'=>$postage,
			'u_country'=>$country,
			'u_indId'=>$indId,
			'u_comMainIndustry'=>$mainIndustry,
			'u_shopUrl'=>$shopUrl,
			'u_companyType' => $companyType,
			'u_isBranch'    => $isBranch,
			'u_companyThree' => $companyThree,
		);
		$authList = [
			'company' =>[2,3,4,5,6,7,8,9,10,11,12],
			'mobile'  => [0],
			'email'   => [1], 
		];
		$userInfo = $user->getFulluserInfo($id);
		$authInfo = $userInfo['userAuthInfo'];		
		if(!$authInfo['company']['authed'] && $authInfo['company']['authing'] == '0'){//公司认证没有通过,没有正在认证,可以修改法人名称
			$arr['u_comLegalName'] = $comLegalName;
		}	
		if(!$authInfo['company']['authed'] && $authInfo['company']['authing'] == '0'){//公司认证没有通过,没有正在认证,可以修改公司名称
		    $arr['u_companyName'] = $companyName;
		}
		if(!$authInfo['mobile']['authed'] && $authInfo['mobile']['authing'] == '0'){//手机认证没有通过,没有正在认证,可以修改手机
			$arr['u_tel'] = $mobile;
		}
		if(!$authInfo['email']['authed'] && $authInfo['email']['authing'] == '0'){//Email认证没有通过,没有正在认证,可以修改Email
			$arr['u_email'] = $email;
		}
		if(!$authInfo['union']['authed'] && $authInfo['union']['authing'] == '0'){//Email认证没有通过,没有正在认证,可以修改Email
		    $arr['u_comArea'] = $comArea;
		}
		if(!$authInfo['union']['authed'] && $authInfo['union']['authing'] == '0'){//Email认证没有通过,没有正在认证,可以修改Email
		    $arr['u_comLeadName'] = $comLeadName;
		}

		$db = new MySql();
		$userInfo = $user->getUserByID($id);
        $type = array( '0' => '否', '1' => '是');
        $companyTypes = F::getAttrs(4);
		try{
		    $db->beginTRAN();

            $uid = "u_id ='".$id."'";
            $result = $db->update("t_user",$arr,$uid);
            if(!$result){
                throw new Exception(-1);
            }
            $userTran = array(
                'ut_uid'	  => $id,
                'ut_type'	  => 9,
                'ut_eid'	  => $_SESSION['userID'],
                'ut_ctime'	  => F::mytime(),
            );
            if($userInfo['u_companyType'] != $companyType){
                $userInfo['u_companyType'] = isset($userInfo['u_companyType']) ? $userInfo['u_companyType'] : '无';
                $userTran['ut_oldValue'] = '企业类型：'.$companyTypes[$userInfo['u_companyType']];
                $userTran['ut_newValue'] = $companyTypes[$companyType];
                $userTran['ut_reason']   = '企业类型转换';
                $change = $db->insert("t_user_tran", $userTran);
                if(!$change){
                    throw new Exception('-1');
                }
            }
            if($userInfo['u_isBranch'] != $isBranch){
                $userInfo['u_isBranch'] = isset($userInfo['u_isBranch']) ? $userInfo['u_isBranch'] : '无';
                $userTran['ut_oldValue'] = '分公司：'.$type[$userInfo['u_isBranch']];
                $userTran['ut_newValue'] = $type[$isBranch];
                $userTran['ut_reason']   = '是否分公司转换';
                $change1 = $db->insert("t_user_tran", $userTran);
                if(!$change1){
                    throw new Exception('-1');
                }
            }
            if($userInfo['u_companyThree'] != $companyThree){
                $userInfo['u_companyThree'] = isset($userInfo['u_companyThree']) ? $userInfo['u_companyThree'] : '无';
                $userTran['ut_oldValue'] = '三证合一：'.$type[$userInfo['u_companyThree']];
                $userTran['ut_newValue'] = $type[$companyThree];
                $userTran['ut_reason']   = '是否三证合一转换';
                $change2 = $db->insert("t_user_tran", $userTran);
                if(!$change2){
                    throw new Exception('-1');
                }
            }

            $db->commitTRAN();
		}catch (Exception $e){
            $db->rollBackTRAN();
            return $this->show(message::getJsonMsgStruct('1002','操作失败'));
		}


        //记录操作日志
        $arr['memo'] = '企业资料修改';//尽量写得详细一点点了
//        log::writeLogMongo(60101, 't_user', $id, $arr);
        
		if($result = 1){
			$this->show(message::getJsonMsgStruct('1001','修改成功'));
		}else{
			$this->show(message::getJsonMsgStruct('1002','修改失败，没有更新'));
		}
  }
}
