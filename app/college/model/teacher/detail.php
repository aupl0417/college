<?php

class detail extends member {
    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        $this->setHeadTag('title', '详细资料-唐人大学'.SEO_TITLE);
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }
        
        $data = [
            'code' => 50202,
            'isLogin' => 1,
            'userNick' => $_SESSION['userNick']
        ];
        
        //'公开课', '定制课', '其它'
        $userInfo = array();
        $field = 'id as userId,username,trueName,mobile,email,avatar,br_name as branchName,tl_name as level,if(te_source=3,"外聘讲师","大唐讲师") as source,te_courseReward,te_workExperience,CASE te_teachGrade WHEN 0 THEN "公开课" WHEN 1 THEN "定制课" ELSE "其它" END as te_teachGrade,te_photo,te_description,if(te_sex=0,"女","男") as te_sex';
        $sql   = 'select ' . $field . ' from tang_ucenter_member
                 LEFT JOIN tang_branch on br_id=tangCollege
                 LEFT JOIN tang_teacher on id=te_userId
                 LEFT JOIN tang_teacher_level on tl_id=te_level
                 where userId="' . $_SESSION['userID'] . '" and identityType=1';

        $userInfo = $this->db->getRow($sql);
        if($userInfo){
            $userInfo['te_photo'] = $this->getTeacherPhoto($userInfo['te_photo'], $userInfo['te_sex']);
        }

        $this->setReplaceData($userInfo);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

    function getTeacherPhoto($photo, $sex){
        if(!$photo){
            if($sex == 1){
                $avatar = 'https://image.dttx.com/v1/tfs/T1S2_TByKT1RCvBVdK.jpg';
            }else {
                $avatar = 'https://image.dttx.com/v1/tfs/T1q0KTB5WT1RCvBVdK.jpg';
            }
        }

        return $photo ? TFS_APIURL . '/' . $photo : $avatar;
    }

}
