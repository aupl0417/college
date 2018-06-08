<?php

class addTeacherLevel_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030103]);			
    }
    
    function run() {
//         dump($this->options);die;
		$name = $this->options['name'];
		if(F::isEmpty($name)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写讲师等级名称'));exit;
		} 
		
		$badgeName = $this->options['badgeName'];
		if(F::isEmpty($badgeName)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写徽章名称'));exit;
		}
		
		$logo = $this->options['logo'];
        if(F::isEmpty($logo)){
		    $this->show(message::getJsonMsgStruct('1002',  '请上传徽章图片'));exit;
		}
		
		$courseLevel = $this->options['courseLevel'];
		if(F::isEmpty($courseLevel)){
		    $this->show(message::getJsonMsgStruct('1002',  '请选择可授课等级'));exit;
		}
		
		$courseType = $this->options['courseType'];
		if(F::isEmpty($courseType)){
		    $this->show(message::getJsonMsgStruct('1002',  '请选择授课类型'));exit;
		}
		
		$condition = $this->options['condition'];
		if(F::isEmpty($condition)){
		    $this->show(message::getJsonMsgStruct('1002',  '请填写晋升条件'));exit;
		}
		
		$db = new MySql();
		$data = array(
		    'tl_name'        => $name,
		    'tl_badgeName'   => $badgeName,
		    'tl_logo'        => $logo,
		    'tl_courseLevel' => $courseLevel,
		    'tl_courseType'  => $courseType,
		    'tl_condition'   => $condition,
		);
		
	    $id = $db->insert('tang_teacher_level', $data);
	    if(!$id){
	        $this->show(message::getJsonMsgStruct('1002',  '添加失败'));
	        exit;
	    }
	    
	    $this->show(message::getJsonMsgStruct('1001',  '编辑成功'));
	    exit;
    }
    
}
