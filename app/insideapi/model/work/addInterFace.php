<?php

class addInterFace extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {
        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }
        //获取发布者的信息
        $sql = "select u_nick from t_user where u_id = '".$_SESSION['userID']."'";
        $nick = $this->db->getRow($sql);
        $info = array('nick'=>$nick['u_nick']);

        $sql = "select * from t_interface_category where ic_father_id = '0'";
        $data = $this->db->getAll($sql);

        $this->setLoopData('categoryData',$data);
        $this->setReplaceData($info);
        $this->setTempAndData();
        $this->show();
    }

}
