<?php

class resourceList extends member {
	
    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }
	
    function run() {
        (!isset($this->options['id']) || empty($this->options['id'])) && header('Location:' . U('college/teacher/courseList'));
        
        $resourceId = $this->options['id'] + 0;
        
        $data = [
            'code' => 50203,
            'isLogin' => 1,
            'userNick' => $_SESSION['userNick'],
            'id'       => $resourceId
        ];
        
        $this->setReplaceData($data);
		$this->setTempAndData();
        $this->show();
    }

}
