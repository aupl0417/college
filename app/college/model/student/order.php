<?php

class order extends member {
	function __construct($options) {
        parent::__construct($options, [50103]);
        $this->db = new MySql();
    }

    function run() {
        $data = [
            'code' => 50103,
        ];
        $this->setHeadTag('title', '我的订单-唐人大学'.SEO_TITLE);
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $userInfo = apis::request('college/api/getUser.json', ['userId'=>$_SESSION['userID']], true);
        if($userInfo['code'] !== '1001'){
            header('Location:https://u.dttx.com/register/index');
        }
        $userInfo = $userInfo['data'];
        $userId = $userInfo['userId'];

        $userInfo['level'] = $userInfo['level'] < 3 ? '消费商会员' : ($userInfo['level'] == 3 ? '创客会员' : '创投会员');
        $learningClass = $this->db->getField('select count(cs_id) from tang_class_student left join tang_class on cs_classId=cl_id where cl_status=1 and cl_state=1 and cl_isTest=0 and cs_studentId="' . $userId . '"');
        $userInfo['learningClass'] = $learningClass ? $learningClass : 0;
        $payingOrder = $this->db->getField('select count(tse_id) from tang_student_enroll where tse_status=0 and tse_state<>-1 and tse_userId="' . $userId . '"');
        $userInfo['payingOrder'] = $payingOrder ? $payingOrder : 0;
        $learnedClass = $this->db->getRow('select count(cs_id) as count,SUM(cs_score) as score from tang_class_student LEFT JOIN tang_class on cs_classId=cl_id where cl_status=1 and cl_state=2 and cl_isTest=0 and cs_studentId="' . $userId . '"');
        $userInfo['learnedClass'] = $learnedClass['count'] ? $learnedClass['count'] : 0;
        $userInfo['score'] = $learnedClass['score'] ? $learnedClass['score'] : 0;
        $certificate = $this->db->getField('select count(tce_id) from tang_certificate where tce_userId="' . $userId . '"');
        $userInfo['certificate'] = $certificate ? $certificate : 0;
//        dump($userInfo);die;
        $this->setReplaceData($userInfo);
        $this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
    }

}
