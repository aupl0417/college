<?php

class deleteReturns_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            header('location:http://'.INSIDEAPI.'/index');
        }

        $sql = "DELETE FROM t_interface_response_field WHERE `irf_id`= '".$this->options['id']."' limit 1";
        //echo $sql;

        if($this->db->exec($sql)){
            $this->show(message::getJsonMsgStruct(1001,'删除成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'删除失败'));
        }

    }

}
