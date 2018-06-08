<?php

class courseList extends member {
    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
		$this->setHeadTag('title', '课程管理-唐人大学'.SEO_TITLE);
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $data = [
            'code' => 50203,
            'isLogin' => 1,
            'userNick' => $_SESSION['userNick']
        ];
        
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}
