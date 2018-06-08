<?php

class addResource extends member {

    function __construct($options) {        		
        parent::__construct($options, [502]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50203,
			'tempId'		=> 'temp_'.F::getGID()
		);
		
		$qiniu = new QiniuStorage();
		$param = array();
		$token = $qiniu->UploadToken($qiniu::SECRETKEY, $qiniu::ACCESSKEY, $param);
		$data['token'] = $token;
		$data['domain'] = $qiniu->DOMAIN;
		$db  = new MySql();
		$userId = $db->getField('select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '" and identityType=1');
		$courseIds = $db->getAll("select distinct cta_courseId from tang_class_table where cta_teacherId='" . $userId . "'");
		
        $sql = "select co_id, co_name from tang_course where co_state=1";//暂时全部选择，以后根据所在分院及下属分院来获取
        if($userId && $courseIds){
            $sql .= " and co_id in (" . implode(',', array_column($courseIds, 'cta_courseId')) . ")";
        }
        
        $courseList = $db->getAll($sql);
        
        $this->setLoopData('courseList', $courseList);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
