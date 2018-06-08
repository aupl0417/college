<?php

class editApplication extends guest {
    function __construct($options) {        		
        parent::__construct($options, [8]);
    }

    function run() {

        $db = new MySql();
        $sql = "select * from t_develop_application where da_id = '".$this->options['id']."' limit 1";
        $data = $db->getRow($sql);

        $temp = array(
            'da_name' => $data['da_name'],
            'da_platform' => $data['da_platform'],
            'da_class' => $data['da_class'],
            'da_icon' => $data['da_icon'],
            'da_briefing' => $data['da_briefing'],
            'da_scene' => $data['da_scene'],
            'da_screenshot' => $data['da_screenshot'],
            'da_domain' => $data['da_domain'],
            'da_server_ip' => $data['da_server_ip'],
            'da_callback' => $data['da_callback'],
            'da_status' => $data['da_status'],
            'da_app_key' => $data['da_app_key'],
            'da_secret_key' => $data['da_secret_key'],
            'da_mome' => $data['da_mome'],
            'da_createtime' => $data['da_createtime'],
            'da_contact' => $data['da_contact'],
            'da_tel' => $data['da_tel'],
            'da_email' => $data['da_email'],
        );


        $this->setReplaceData($temp);
		$this->setTempAndData();
		$this->show();
    }

}