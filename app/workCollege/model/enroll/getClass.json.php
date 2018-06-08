<?php

class getClass_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010503]);
    }

    public function run(){
        $db = new MySql();
        
        if(isset($this->options['username']) || isset($this->options['mobile'])){
            $username = trim($this->options['username']);
            $mobile   = trim($this->options['mobile']);

            if (empty($username) && empty($mobile)) {
                die($this->show(message::getJsonMsgStruct('1002', '请输入报名人账号或手机号码')));
            }

            $where = ' WHERE 1'; 
            $where .= !empty($username) ? " AND username='{$username}'" : " AND mobile='{$mobile}'"; 
            
            $userInfo = $db->getRow("SELECT id,username,mobile,trueName FROM tang_ucenter_member $where AND identityType=0");
            !$userInfo && die($this->show(message::getJsonMsgStruct('1002', '用户名信息错误,未找到该学员')));
            
            $sql = "SELECT cl_id AS id,cl_name AS className FROM tang_student_enroll
                LEFT JOIN tang_class ON cl_id=tse_classId WHERE cl_state IN (0,1) and cl_status=1  AND tse_userId='{$userInfo['id']}'";

            $classList = $db->getAll($sql);
            !$classList && die($this->show(message::getJsonMsgStruct('1002', '报名人未报名班级,或者该班级不在允许报到状态')));

            $res = array('userInfo'=>$userInfo,'classList'=>$classList);
            die($this->show(message::getJsonMsgStruct('1001', $res)));
        }else{
            //empty($this->options['className']) && die($this->show(message::getJsonMsgStruct('1002', '请输入班级名称')));

            $className = $this->options['className'];
			if(empty($className)){
				$sql = 'select cl_id as id,cl_name as className from tang_class where 1 and cl_state IN(0,1) and cl_status=1';
			}else{
				$sql = 'select cl_id as id,cl_name as className from tang_class where cl_name like "' . $className .'%" and cl_state IN(0,1) and cl_status=1';
			}
            
        }

        $classList = $db->getAll($sql);
        !$classList && die($this->show(message::getJsonMsgStruct('1002', '该班级不在允许报到状态')));

        $this->show(message::getJsonMsgStruct('1001', $classList));
    }
}
