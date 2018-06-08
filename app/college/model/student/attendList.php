<?php

class attendList extends member {
	function __construct($options) {
        parent::__construct($options, [50102]);
        $this->db = new MySql();
    }

    function run() {
        $id = $this->options['id'] + 0;
        $data = [
            'code' => 50102,
            'classId' => $id
        ];
        $this->setHeadTag('title', '签到记录-唐人大学'.SEO_TITLE);
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
    }

}
