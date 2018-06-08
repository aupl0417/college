<?php

class interactionManage extends member {
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
            'id'       => $courseId,
            'cId'      => $classId
        ];
        
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}
