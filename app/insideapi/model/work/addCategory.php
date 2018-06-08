<?php

class addCategory extends guest {
    function __construct($options) {
        parent::__construct($options, [8]);
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }

        $this->setTempAndData();
        $this->show();
    }

}
