<?php

class reviewed extends guest {
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
        //获取接口分类数据
        $sql = "select * from t_interface_category where ic_father_id = '0'";
        $categoryData = $this->db->getAll($sql);

        //获取全部接口数据
        $sql = "select * from t_interface_list";
        $apiList = $this->db->getAll($sql);
        $this->setLoopData('apilist',$apiList);
        $this->setLoopData('categoryData',$categoryData);
        $this->setTempAndData();
        $this->show();

    }

}
