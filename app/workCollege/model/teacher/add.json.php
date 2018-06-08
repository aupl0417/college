<?php

class add_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030102]);			
    }
    
    function run() {
//         if($this->options['eduLevel'] == ''){
//             echo 'empty';
//         }
//         dump($this->options);die;
		$source = $this->options['source'];
		if(F::isEmpty($source)){
		    $this->show(message::getJsonMsgStruct('1002',  '请选择讲师来源'));exit;
		} 
		
	    $username = $this->options['username'];
	    if(F::isEmpty($username)){
	        $this->show(message::getJsonMsgStruct('1002',  '会员名不能为空'));exit;
	    }
		
		$trueName = $this->options['trueName'];
		if(F::isEmpty($trueName)){
		    $this->show(message::getJsonMsgStruct('1002',  '真实姓名不能为空'));exit;
		}
		
		$mobile = $this->options['mobile'];
		if(F::isEmpty($mobile)){
		    $this->show(message::getJsonMsgStruct('1002',  '手机号码不能为空'));exit;
		}
		
		if(!F::regularCheck(2, $mobile)){
		    $this->show(message::getJsonMsgStruct('1002',  '手机号码格式错误'));exit;
		}
		
		$email = $this->options['email'];
		if(F::isEmpty($email)){
		    $this->show(message::getJsonMsgStruct('1002',  '邮箱不能为空'));exit;
		}
		
		if(!F::regularCheck(3, $email)){
		    $this->show(message::getJsonMsgStruct('1002',  '邮箱格式错误'));exit;
		}
		
		$IDNum = $this->options['IDNum'];
		if(F::isEmpty($IDNum)){
		    $this->show(message::getJsonMsgStruct('1002',  '身份证号码不能为空'));exit;
		}
		
		if(($info = idcard::idcard_checkIDCard($IDNum)) != 1){
		    $this->show(message::getJsonMsgStruct('1002',  $info));exit;
		}
		
		$teacherLevel = $this->options['teacherLevel'];
		if(F::isEmpty($teacherLevel)){
		    $this->show(message::getJsonMsgStruct('1002',  '请选择讲师等级'));exit;
		}
		
		$workExperience = $this->options['workExperience'];
		if(F::isEmpty($workExperience)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写讲师从业经历'));exit;
		}
		
		$courseReward = $this->options['courseReward'];
		if(F::isEmpty($courseReward)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写讲师课时报酬'));exit;
		}
		
		if(!is_numeric($courseReward)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写讲师课时报酬为数字'));exit;
		}
		
		if($courseReward <= 0){
		    $this->show(message::getJsonMsgStruct('1002',  '讲师课时报酬不能为负数'));exit;
		}
		
		$teachGrade = $this->options['teachGrade'];
		if(F::isEmpty($teachGrade)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写讲师授课类型'));exit;
		}

		$logo = $this->options['logo'];
		
		//调用获取用户数据接口
	    $params['input'] = $username;
	    $path = '/user/getUser';
	    $sdk  = new openSdk();
	    $result = $sdk->request($params, $path);
	    	
	    !is_array($result) && die($this->show(message::getJsonMsgStruct('1002', $result)));
	    
	    $userInfo = $result['info'];
		
		$db = new MySql();
		
		$ucenterData = array(
		    'username'     => $username,
		    'trueName'     => $trueName,
		    'mobile'       => $mobile,
		    'certNum'      => $IDNum,
		    'email'        => $email,
		    'identityType' => 1,
		    'tangCollege'  => $this->options['branchId'] == '' ? 0 : $this->options['branchId'] + 0
		);
		
		$teacherData = array(
		    'te_source'        => $source + 0,
			'te_photo'		   => $logo,
		    'te_IDNum'         => $IDNum,
		    'te_workExperience'=> $workExperience,
		    'te_sex'           => $this->options['sex'] + 0,
		    'te_courseReward'  => $courseReward,
		    'te_teachGrade'    => $teachGrade + 0,
		    'te_level'         => $teacherLevel + 0,
		    'te_eduLevel'      => $this->options['eduLevel'] == '' ? 3 : $this->options['eduLevel'] + 0,
		    'te_birthday'      => $this->options['birthday'],
		    'te_description'   => $this->options['description'],
		    'te_isLeave'       => $this->options['isLeave']
		);
		
		try{
		    $db->beginTRAN();
    		if(in_array($source, array(1,2))){
    		    !$userInfo && die($this->show(message::getJsonMsgStruct('1002', '用户不存在')));
    		    
    	        $ucenterData['certType']     = $userInfo['certType'];
    	        $ucenterData['avatar']       = $userInfo['avatar'];
    	        $ucenterData['level']        = $userInfo['level'];
    	        $ucenterData['auth']         = $userInfo['auth'];
    	        $ucenterData['authImage']    = $userInfo['au_authImg'] ? serialize($userInfo['au_authImg']) : '';
    	        $ucenterData['type']         = $userInfo['type'];
    	        $ucenterData['code']         = $userInfo['code'];
    	        
    		    $user = $db->getRow('select id,identityType,mobile,email,certNum from tang_ucenter_member where username="' . $username . '"');
    		    
                if($user){
                    if($user['identityType'] == 1){
                        throw new Exception('该用户已是讲师');
                    }
                    if($user['mobile'] != $mobile){
                        throw new Exception('手机号码不正确');
                    }
                    
                    if(!empty($user['email']) && $user['email'] != $email){
                        throw new Exception('邮箱不正确');
                    }
                    
                    if(!empty($user['certNum']) && $user['certNum'] != $IDNum){
                        throw new Exception('身份证号码不正确');
                    }
                    
                    $res = $db->update('tang_ucenter_member', $ucenterData, 'username="' . $username . '"');
                    if($res === false){
                        throw new Exception('插入用户表失败');
                    }
                    
                    $teacherData['te_userId'] = $user['id'];//讲师id
                    
                }else {
                    $ucenterData['userId'] = $userInfo['id'];
                    $id = $db->insert('tang_ucenter_member', $ucenterData);
                    if(!$id){
                        throw new Exception('插入用户表失败');
                    }
                    
                    $teacherData['te_userId'] = $db->getLastID();  //讲师id
                }
                
                $tid = $db->insert('tang_teacher', $teacherData);
                if(!$tid){
                    throw new Exception('插入教师表失败');
                }
                
                $db->commitTRAN();
                
                die($this->show(message::getJsonMsgStruct('1001',  '添加成功')));
    		}else {
    		    $userInfo && die($this->show(message::getJsonMsgStruct('1002', '用户已存在')));
    		    
		        $count = $db->getField('select count(id) from tang_ucenter_member where username="' . $username . '"');
		        $count && die($this->show(message::getJsonMsgStruct('1002',  '该用户名已存在')));
    		    
		        if($this->check('mobile', $mobile)){
		            throw new Exception('手机号码已存在');
		        }
		        
		        if($this->check('email', $email)){
		            throw new Exception('邮箱已存在');
		        }
		        
		        if($this->check('certNum', $IDNum)){
		            throw new Exception('身份证号码已存在');
		        }
		        
		        $ucenterData['userId']       = F::getGID(32);
		        $ucenterData['password']     = F::getSuperMD5('123456');
		        $ucenterData['isFromErp']    = 0;
    		    
		        $id = $db->insert('tang_ucenter_member', $ucenterData);
		        if(!$id){
		            throw new Exception('插入用户表失败');
		        }
		        $teacherData['te_userId'] = $db->getLastID();
		        $tid = $db->insert('tang_teacher', $teacherData);
		        if(!$tid){
		            throw new Exception('插入教师表失败');
		        }
                
		        $db->commitTRAN();
                
		        die($this->show(message::getJsonMsgStruct('1001',  '添加成功')));
    		}
		}catch (Exception $e){
		    $db->rollBackTRAN();
		    $this->show(message::getJsonMsgStruct('1002',  $e->getMessage()));
		    exit;
		}
    }
    
    private function check($key, $value){
        $db = new MySql();
        $count = $db->getField('select count(id) from tang_ucenter_member where ' . $key . '="' . $value . '"');
        if($count){
            return true;
        }
        
        return false;
    }
}
