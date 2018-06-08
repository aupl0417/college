<?php

class info extends member {
    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        $this->setHeadTag('title', '讲师信息-唐人大学'.SEO_TITLE);
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }
        
        $data = [
            'code' => 50201,
            'events' => '',
            'today'   => date('Y-m-d'),
            'isLogin' => 1,
            'userNick' => $_SESSION['userNick']
        ];
        
        $userInfo = apis::request('college/api/getUserInfo.json', ['userId'=>$_SESSION['userID']], true);
        if($userInfo['code'] !== '1001'){
            header('Location:https://u.dttx.com/register/index');
        }
//        dump($userInfo);die;


        $userInfo = $userInfo['data'];
        $userId = $userInfo['userId'];

        if($userInfo['identityType'] == 0){
            header('Location:' . U('college/student/index'));//如果不是讲师身份，则自动转到学员首页（暂时）
        }

        $level = $this->db->getRow('select tl_name,te_source from tang_teacher LEFT JOIN tang_teacher_level on te_level=tl_id where te_userId="' . $userId . '"');
        $userInfo['level'] = $level['tl_name'] ? $level['tl_name'] : '';
        $userInfo['source'] = $level['te_source'] == 3 ? '外聘讲师' : '大唐讲师';
        //待备课数
        $day = date("t",strtotime(date('Y-m')));
        $monthEndDate = date('Y-m') . '-' . $day . ' 23:59:59';
        $courseCount = $this->db->getField('select count(cta_id) from tang_class_table where cta_teacherId="' . $userId . '" and cta_startTime>="' . date('Y-m-d H:i:s') . '" and cta_startTime<="' . $monthEndDate . '"');
        $userInfo['courseCount'] = $courseCount ? $courseCount : 0;

        //待回复提问
        $replyList = $this->db->getAll('select tsi_id from tang_teacher_student_interaction where tsi_teacherId="' . $userId .'" and tsi_pid=0');
        $userInfo['replyCount'] = 0;
        foreach($replyList as $key=>$val){
            $reply = $this->db->getField('select tsi_id from tang_teacher_student_interaction where tsi_pid="' . $val['tsi_id'] . '" and tsi_teacherId="' . $userId . '"');
            if(!$reply){
                $userInfo['replyCount'] ++;
            }
        }

        //已授总课时
        $teachedCourse = $this->db->getALL('select cta_startTime,cta_endTime from tang_class_table where cta_teacherId="' . $userId . '" and cta_endTime<="' . date('Y-m-d H:i:s') . '"');
//        dump($teachedCourse);die;
        $sum = 0;
        foreach($teachedCourse as $val){
            $sum += strtotime($val['cta_endTime']) - strtotime($val['cta_startTime']);
        }
        $userInfo['totalTeachTime'] = round($sum / (60 * 60), 2);

        //我的总收益（暂时算授课费用）
        $income = $this->db->getField('select SUM(tti_total) from tang_teacher_income where tti_userId="' . $userId . '"');
        $userInfo['income'] = $income ? $income : 0;

        $this->setReplaceData($userInfo);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}
