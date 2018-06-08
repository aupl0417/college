<?php

class deleteInterFace_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            header('location:http://'.INSIDEAPI.'/index');
        }
        //查询用户信息
        $sql = "select u_nick from t_user where u_id = '".$_SESSION['userID']."' limit 1";
        $user = $this->db->getRow($sql);

        //查询接口信息
        $sql = "select il_author from t_interface_list where il_id = '".$this->options['id']."' limit 1";
        $interface = $this->db->getRow($sql);

        if($user['u_nick'] == $interface['il_author'] || $_SESSION['userID'] == 'd74d0e8859bdcea241b6895a6706fdc4'){
            $sql = "DELETE FROM t_interface_list WHERE `il_id`= '".$this->options['id']."' limit 1";
            $sql_RQ = "delete from t_interface_request_field where iqf_il_id = '".$this->options['id']."'";
            $sql_RP = "delete from t_interface_response_field where irf_il_id = '".$this->options['id']."'";
            if($this->db->exec($sql)){
                $this->db->exec($sql_RQ);
                $this->db->exec($sql_RP);
                $this->show(message::getJsonMsgStruct(1001,'删除成功'));
            }else{
                $this->show(message::getJsonMsgStruct(1002,'删除失败'));
            }
        }else{
            $this->show(message::getJsonMsgStruct(1002,'请删除自己发布的接口，无法删除别人发布的接口！'));
        }


    }

}
