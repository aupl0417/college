<?php

class edit_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);			
    }
    
    function run() {
		$id = $this->options['id'];
		
		if(F::isEmpty($id)){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));
		}
		
		$courseReward = $this->options['courseReward'];
		if(F::isEmpty($courseReward)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写讲师课时报酬'));exit;
		}

//		$logo = $this->options['logo'];
//		if(F::isEmpty($logo)){
//			$this->show(message::getJsonMsgStruct('1002',  '请上传讲师照片'));exit;
//		}
		
		if(!is_numeric($courseReward)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写讲师课时报酬为数字'));exit;
		}
		
		if($courseReward <= 0){
		    $this->show(message::getJsonMsgStruct('1002',  '讲师课时报酬不能为负数'));exit;
		}
		
		$db = new MySql();
		$ucenterData = array(
		    'tangCollege' => $this->options['branchId'] + 0
		);
		
		$teacherData = array( 
		    'te_teachGrade'   => $this->options['teachGrade'] + 0,
		    'te_level'        => $this->options['level'] + 0,
		    'te_courseReward' => $courseReward,
		    'te_description'  => $this->options['description'],
			//'te_photo'        => $logo,
			'te_isLeave'        => $this->options['isLeave'] + 0
		    //'te_source'       => $this->options['source'] + 0
		);
		if($this->options['logo']){
			$teacherData['te_photo'] = $this->options['logo'];
		}

		try{
		    $db->beginTRAN();
		    $result = $db->update('tang_ucenter_member', $ucenterData, 'id="' . $id . '"');
		    
		    if($result === false){
		        throw new Exception('更新用户表失败');
		    }
		    
		    $res = $db->update('tang_teacher', $teacherData, 'te_userId="' . $id . '"');
		    
		    if($res === false){
		        throw new Exception('更新教师表失败');
		    }
		    
		    $db->commitTRAN();
		    
		    $this->show(message::getJsonMsgStruct('1001',  '编辑成功'));
		    exit;
		}catch (Exception $e){
		    print_r($db->getError($e));die;
			$db->rollBackTRAN();
			$this->show(message::getJsonMsgStruct('2099',  '编辑失败'));
			exit;
		}
		
    }
}
