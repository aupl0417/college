<?php

class editInterFace extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }

        $sql = "select * from t_interface_category where ic_father_id = '0'";
        $data = $this->db->getAll($sql);
        $data = array_column($data, 'ic_name', 'ic_id');

        $sqlinterface = "select * from t_interface_list where il_id = '".$this->options['id']."'";
        $interface = $this->db->getRow($sqlinterface);

        $temp = array(
            'il_id' => $interface['il_id'],
            'il_title' => $interface['il_title'],
            'il_name' => $interface['il_name'],
            'il_interface_url' => $interface['il_interface_url'],
            'il_ic_id' =>  F::array2Options($data,[$interface['il_ic_id']]),
            'il_description' => $interface['il_description'],
            'il_example' => $interface['il_example'],
        );

        //dump($temp['il_ic_id']);
        $this->setLoopData('categoryData',$data);
        $this->setReplaceData($temp);
        $this->setTempAndData();
        $this->show();
    }

}
