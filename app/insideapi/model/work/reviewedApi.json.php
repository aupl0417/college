<?php

class reviewedApi_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }
        if($_SESSION['userID'] != 'd74d0e8859bdcea241b6895a6706fdc4'){
            echo '<script>alert("你没有权限！",location.href="http://'.INSIDEAPI.'/work/interfaceList")</script>';
        }
        $options = $this->options;
        $p = array(
            'il_reviewed' => 1,
        );

        if($this->db->update('t_interface_list',$p,"il_id='".$options['id']."'")){
            $this->show(message::getJsonMsgStruct(1001,'修改成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'修改失败'));
        }

    }

}
