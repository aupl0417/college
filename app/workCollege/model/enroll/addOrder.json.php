<?php

class addOrder_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50010503]);
    }

    function run() {
        $options = $this->options;
        (!isset($options['userInfo']) || empty($options['userInfo'])) && die($this->show(message::getJsonMsgStruct('1002', '请填写用户名或者联系电话')));
        (!isset($options['state']) || $options['state'] == '') && die($this->show(message::getJsonMsgStruct('1002', '请选择状态')));
        (!isset($options['classId']) || empty($options['classId'])) && die($this->show(message::getJsonMsgStruct('1002', '请选择班级')));
        
        $db = new MySql();
        log::writeLogMongo(4564783, 'sdf', '', $options);
        
        //检查该班级是否存在
        $classInfo = $db->getRow('select cl_name,cl_description,cl_cost from tang_class where cl_id="' . $options['classId'] . '" and cl_state IN(0,1) and cl_status=1');//班级信息
        !$classInfo && die($this->show(message::getJsonMsgStruct('1002', '该班级不存在')));
		
		if (preg_match('/^1[3|4|5|7|8]\d{9}$/', $options['userInfo'])) {
			$userName = '';
			$mobile = $options['userInfo'];
		}
		else{
			$userName = $options['userInfo'];
			$mobile = '';
		}       
		$classId = $options['classId'] - 0;
		$param = [
			'userId'	=> '',
			'userName'	=> $userName,
			'mobile'	=> $mobile,
			'spec'		=> 1,
			'classId'	=> $classId,
			'isApp'     => 0
		];
		
		$result = apis::request('/college/api/enroll.json', $param ,true);
		log::writeLogMongo(4564783, 'sdf', '', $result);
		if(isset($result['code'])){
			if($result['code'] == '1001'){
				$this->show(message::getJsonMsgStruct('1001', '操作成功!'));
			}
			else{
				$this->show(message::getJsonMsgStruct('1002', ''. $result['data'] .''));
			}
		}else{
			$this->show(message::getJsonMsgStruct('1002', '系统错误'));
		}
        
        
    }
}
