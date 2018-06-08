<?php

class index extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {

        $this->setHeadTag('title', '学院简介-唐人大学'.SEO_TITLE);

        $this->setReplaceData(['menu'=>$this->options['PATH_MODEL']]);
		$this->setTempAndData();
        $this->show();
    }
}
