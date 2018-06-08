<?php

class editInterFace_json extends guest {
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
            'il_description' => $options['il_description'],
            'il_example' => $options['il_example'],
        );

        if($this->db->update('t_interface_list',$p,"il_id='".$options['id']."'")){
            $this->show(message::getJsonMsgStruct(1001,'修改成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'修改失败'));
        }

    }

}
