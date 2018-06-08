<?php

class addReturns extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }

        $data['irf_il_id'] = $this->options['id'];
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}
