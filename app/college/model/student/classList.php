<?php

class classList extends member {
	function __construct($options) {
        parent::__construct($options, [50102]);
        $this->db = new MySql();
    }

    function run() {
        $data = [
            'code' => 50102,
        ];
        $this->setHeadTag('title', '我的班级-唐人大学'.SEO_TITLE);
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
    }

}
