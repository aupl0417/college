<?php

class addRequest extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }

        $data['iqf_id'] = $this->options['id'];
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}
