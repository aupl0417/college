<?php

class getUserInfo_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010503]);
    }

    public function run(){
        $db = new MySql();
        if(isset($this->options['userInfo'])){
            empty($this->options['userInfo']) && die($this->show(message::getJsonMsgStruct('1002', '请填写用户名或联系电话')));
            $info = $this->options['userInfo'];
            if(F::isPhone($info)){
				$param = [
					'mobile'	=> $info,
				];
            }else{
				$param = [
					'userName'	=> $info,
				];				
            }

			
			$result = apis::request('/college/api/getUser.json', $param, true);
			
			if(isset($result['code'])){
				if($result['code'] == '1001'){
					$userInfo = $result['data'];
				}else{
					die($this->show(message::getJsonMsgStruct('1002', $result['data'])));
				}
			}else{
				die($this->show(message::getJsonMsgStruct('1002', '用户不存在')));
			}
        }else {
            (empty($this->options['username']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '请填写会员名')));
            
            $username = $this->options['username'];
            $id       = $this->options['id'];
            
            
            $userInfo = $db->getRow('select id,username,trueName,mobile from tang_ucenter_member where username="' . $username .'"');
            
            !$userInfo && die($this->show(message::getJsonMsgStruct('1002', '用户不存在')));
            $userId = $userInfo['id'];
            
            $oriGrade = $db->getField('select cl_gradeId from tang_student_enroll LEFT JOIN tang_class on tse_classId=cl_id where tse_id="' . $id . '"');
            $purGrade = $db->getField('select cl_gradeId from tang_class_student LEFT JOIN tang_class on cs_classId=cl_id where cs_studentId="' . $userId . '"');
            
            //如果有相同级别的班级，则不能转人
            if($oriGrade === $purGrade){
                die($this->show(message::getJsonMsgStruct('1002', '选择转让的会员已经学习了该级别的课程，请重新选择！')));
            }
        }
        
        $userInfo['trueName'] = $userInfo['trueName'] ? $userInfo['trueName'] : $userInfo['username'];
        $userInfo['mobile'] = F::hidtel($userInfo['mobile']);
        $this->show(message::getJsonMsgStruct('1001', $userInfo));
    }
}
