<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/11
 * Time: 9:58
 */
class deleteApplication_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {
        $result = $this->db->update('t_develop_application',array('da_status'=>4),"da_id = '".$this->options['id']."'");
        if($result !== false){
            $this->show(message::getJsonMsgStruct(1001,'删除成功'));
        }
        else{
            $this->show(message::getJsonMsgStruct(1002,'删除失败'));
        }

    }

}