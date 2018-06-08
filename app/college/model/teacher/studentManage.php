<?php

class studentManage extends member {
    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        (!isset($this->options['id']) || empty($this->options['id'])) && header('Location:' . U('college/teacher/courseList'));//如果参数错误暂时跳转到课程列表
        (!isset($this->options['id']) || empty($this->options['id'])) && header('Location:' . U('college/teacher/courseList'));//如果参数错误暂时跳转到课程列表
        
        $this->setHeadTag('title', '学员管理-唐人大学'.SEO_TITLE);
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }
        
        
        
        $classId = $this->options['cId'] + 0;//班级id
        $courseId = $this->options['id'] + 0; //课程id
        
        $data = [
            'code'     => 50203,
            'isLogin'  => 1,
            'userNick' => $_SESSION['userNick'],
            'id'       => $courseId,
            'cId'      => $classId
        ];
        
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
