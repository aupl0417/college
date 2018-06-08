<?php

class addInterFace_json extends guest {
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
            'il_ic_id' => $options['il_ic_id'],
            'il_name' => $options['il_name'],
            'il_title' => $options['il_title'],
            'il_interface_url' => $options['il_interface_url'],
            'il_create_time' => F::mytime(),
            'il_description' => $options['il_description'],
            'il_author' => $options['il_author'],
            'il_example' => $options['il_example'],
            'il_reviewed' => 2,
        );

        if($this->db->insert('t_interface_list',$p)){
            $this->show(message::getJsonMsgStruct(1001,'发布成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'发布失败'));
        }

    }

}
