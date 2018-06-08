<?php

class person_json extends worker {
    function __construct($options) {        		
        parent::__construct($options, [60101]);			
    }

  function run() {
        $user = new user();
        $data = $this->options;
        
        $id = isset($data['userid']) ? $data['userid'] : '';
        
        if($id == ''){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;
        }
        
        $country = isset($data['country']) ? $data['country'] : 0;
        if(!F::fmtNum($country)){
            $this->show(message::getJsonMsgStruct('1002', '国家格式错误'));//国家格式为空
            exit;
        }
        $certType = ($country == 37) ? 0 : 1;
        $realname = isset($data['realname']) ? $data['realname'] : '';
        
        if($realname == ''){
            $this->show(message::getJsonMsgStruct('1002', '真实姓名为空'));//真实姓名为空
            exit;
        }
        
        $birth = isset($data['birthday']) ? implode('-', $data['birthday']) : '';
        if($birth == ''){
            $this->show(message::getJsonMsgStruct('1002', '生日为空'));//生日为空
            exit;
        }

        //$birth = substr($data['birthday'],0,4).'-'.substr($data['birthday'],4,2).'-'.substr($data['birthday'],6,2);
        if(!F::isdate($birth)){
            $this->show(message::getJsonMsgStruct('1002', '日期格式错误'));//日期格式错误
            exit;
        }
		//echo $birth.' - ';
		//echo strtotime($birth) .' - '.strtotime('1990-00-00').' - '.time();
        if(strtotime($birth)<strtotime('1900-01-01') || strtotime($birth)>time()){
            $this->show(message::getJsonMsgStruct('1002', '日期错误'));//日期错误
            exit;
        }
        
        $qq = isset($data['qq'])?$data['qq']:"";
        if($qq != ""){
            if(!F::isMaxLength($qq,11)){
                $this->show(message::getJsonMsgStruct('1002', 'qq错误'));//qq错误
                exit;
            }
        }
        $postage = isset($data['postage']) ? $data['postage']: "";
        
        $tel = isset($data['tel'])?$data['tel']:"";
        
        $address = isset($data['address'])? $data['address'] : '';
        if($address==""){
            $this->show(message::getJsonMsgStruct('1002', '地址为空'));//生日为空
            exit;
        }
        if(F::isEmpty($address)){
            $this->show(message::getJsonMsgStruct('1002', '地址不合格'));//生日为空
            exit;
        }
        
        $sex = isset($data['sex'])? $data['sex'] : '';
        if($sex==""){
            $this->show(message::getJsonMsgStruct('1002', '性别为空'));//性别为空
            exit;
        }
        
        $indId = isset($data['indId'])? $data['indId'] : 0;
        if($country == 37){
            $area = isset($data['area'])? $data['area'] : '';
            if($area==""){
                $this->show(message::getJsonMsgStruct('1002', '区域为空'));//区域为空
                exit;
            }
            if(!F::fmtNum($area)){
                $this->show(message::getJsonMsgStruct('1002', '区域格式错误'));//区域格式为空
                exit;
            }
        }else{
            $area = 0;
        }
        $mainIndustry = isset($data['mainIndustry'])? $data['mainIndustry'] : '';
        if($mainIndustry==""){
            //$this->show(message::getJsonMsgStruct('1002', '主营业务为空'));//主营业务为空
            //exit;
        }
        
        $certnum = isset($data['certnum'])? $data['certnum'] : '';
		
        if(!$user->uniqueUserInfo(4, $certnum, $id)){
            $this->show(message::getJsonMsgStruct('1002', '身份证号码已经存在'));//身份证已经存在
            exit;
        }
        
        $phone = isset($data['phone'])? $data['phone'] : '';
        if(!F::fmtNum($phone)){
            $this->show(message::getJsonMsgStruct('1002', '手机错误'));//手机错误
            exit;
        }
        
        if(!$user->uniqueUserInfo(2,$phone, $id)){
             $this->show(message::getJsonMsgStruct('1002', '该号码已经存在'));//手机错误
            exit;
        }
        
        $email = isset($data['email'])? $data['email'] : "";
        if(!F::isEmail($email)){
            $this->show(message::getJsonMsgStruct('1002', 'email写错误'));//eamil错误
            exit;
        }
        if(!$user->uniqueUserInfo(8,$email, $id)){
            $this->show(message::getJsonMsgStruct('1002', '该email已经存在'));//email错误
            exit;
        }
        $shopUrl = isset($data['shopUrl']) ? $data['shopUrl'] : '';
        $comAddress = isset($data['comAddress']) ? $data['comAddress'] : '';
        
        $arr = array(
            'u_name' 			=> $realname,
            'u_birth' 			=> $birth,
            'u_address' 		=> $address,
            'u_qq' 				=> $qq,
            'u_otherTel'		=> $tel,
            'u_area'			=> $area,
            'u_postage'			=> $postage,
            'u_country'			=> $country,
            'u_sex'				=> $sex,
            'u_comMainIndustry' => $mainIndustry,
            'u_indId'			=> $indId,
            'u_shopUrl'			=> $shopUrl,
            'u_comAddress'		=> $comAddress,
            'u_certType'        =>$certType
        );
        
 		$authList = [
			'person' =>[2,3,4,5,6],
			'mobile'  => [0],
			'email'   => [1], 
		];
		$authInfo = $user->getAuthInfo($id, 1, $authList, false);	
//  		dump($authInfo);die;
		if(!$authInfo['person']['authed'] && $authInfo['person']['authing'] == '0'){//公司认证没有通过,没有正在认证,可以修改法人名称
			$arr['u_certNum'] = $certnum;
		}		
		if(!$authInfo['mobile']['authed'] && $authInfo['mobile']['authing'] == '0'){//手机认证没有通过,没有正在认证,可以修改手机
			$arr['u_tel'] = $phone;
		}
		if(!$authInfo['email']['authed'] && $authInfo['email']['authing'] == '0'){//Email认证没有通过,没有正在认证,可以修改Email
			$arr['u_email'] = $email;
		}         
        $db = new MySql();
        $uid = "u_id = '".$id."'";
  
		$result = $db->update("t_user",$arr,$uid);
		
		//记录操作日志
		$arr['memo'] = '个人资料修改';//尽量写得详细一点点了
		log::writeLogMongo(60101, 't_user', $id, $arr);
		
		if($result == 1){
			$this->show(message::getJsonMsgStruct('1001','修改成功'));
		}else{
			$this->show(message::getJsonMsgStruct('1002','修改失败，没有更新'));
		}
    }
}
