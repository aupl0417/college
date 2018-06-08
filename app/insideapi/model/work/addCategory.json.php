<?php

class addCategory_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }

        $options = $this->options;
        $p = array(
            'ic_father_id' => 0,
            'ic_name' => $options['ic_name'],
            'ic_order' => 1,
        );

        if($this->db->insert('t_interface_category',$p)){
            $this->show(message::getJsonMsgStruct(1001,'发布成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'发布失败'));
        }

    }

}
