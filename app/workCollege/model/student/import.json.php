<?php

class import_json extends worker {

    function __construct($options) {
        parent::__construct($options, [50020102]);
    }
    
    function run() {
        if(isset($this->options['username']) && !empty($this->options['username'])){
		    $param = $this->options['username'];
		}else if(isset($this->options['mobile']) && !empty($this->options['mobile'])){
		    $param = $this->options['mobile'];
		}else {
		    return false;//暂时
		}

		$params['input'] = $param;
        $path = '/user/getUser';
        $sdk  = new openSdk();
        $data = $sdk->request($params, $path);
	    
        !is_array($data) && die($this->show(message::getJsonMsgStruct('1002', $data)));
        $userInfo = $data['info'];
		$db = new MySql();
		$classId = $this->options['classId'] + 0;
		$branchId = $db->getField('select tangCollege from tang_class where cl_id="'.$classId . '"');
		$allowCount = $db->getField('select cl_allowableNumber from tang_class where cl_id="'. $classId . '"');
		if($db->getField('select count(cs_classId) from tang_class_student where cs_classId="' . $classId . '"') > $allowCount){
		    $this->show(message::getJsonMsgStruct('1002', '该班人员已满'));//失败
		    exit;
		}
		
		if($db->getField('select count(id) from tang_ucenter_member where username="'.$userInfo['nick'].'"')){
		    $this->show(message::getJsonMsgStruct('1002', '该学员已存在'));//失败
		    exit;
		}
		
        if (0 == $userInfo['type']) {
            $trueName = $userInfo['name'];
        }else{
            $trueName = empty($userInfo['comLegalName']) ? (empty($userInfo['comLeadName']) ? $userInfo['comLeadName'] : '') : $userInfo['comLegalName'];
        }
		$time = time();
		$userData = array(
		    'username'            => $userInfo['nick'],
		    'trueName'            => $trueName,
		    'email'               => $userInfo['email'],
		    'avatar'              => $userInfo['avatar'],
		    'userId'              => $userInfo['id'],
		    'tangCollege'         => $branchId,
		    'mobile'              => $userInfo['tel'],
		    'auth'                => $userInfo['auth'],
		    'certType'            => $userInfo['certType'],
		    'type'                => $userInfo['type'],
		    'level'               => $userInfo['level'],
		    'reg_time'            => $time,
		    'reg_ip'              => ip2long($this->GetIP()),
		    'last_login_time'     => $time,
		    'last_login_ip'       => ip2long($this->GetIP()),
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
		}else if($data['info']['type'] == 0) {
		    $userData['certNum'] = $userInfo['certNum'];
		}
		
		try{
		    $db->beginTRAN();
		    $uid = $db->insert('tang_ucenter_member', $userData);
		    if(!$uid){
		        throw new Exception('插入用户表失败');
		    }
		    
		    $studentInfo = array(
		        'cs_classId' => $classId,
		        'cs_studentId' => $db->getLastID(),
		        'cs_createTime' => date("Y-m-d H:i:s", $time)
		    );
		    
		    $csId = $db->insert('tang_class_student', $studentInfo);
		    if(!$csId){
		        throw new Exception('插入班级学生关联表失败');
		    }
		    
		    $db->commitTRAN();
		    
		    $this->show(message::getJsonMsgStruct('1001','导入成功'));//导入成功
		    exit;
		}catch(Exception $e){
			$db->rollBackTRAN();
			return apis::apiCallback('2099', '导入失败');//导入失败
		}
		
    }
    
    
    private function GetIP(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }elseif(!empty($_SERVER["REMOTE_ADDR"])){
            $cip = $_SERVER["REMOTE_ADDR"];
        }else{
            $cip = "";
        }
        
        return $cip;
    }
    
}
