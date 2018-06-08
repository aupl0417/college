<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/25
 * Time: 10:25
 */
 class userTypeView_json extends worker{
    function __construct($options) {
        parent::__construct($options, [609]);
    }

    function run() {
        $this->options['id'] = isset($this->options['id']) ? $this->options['id'] : '' ;
        $db = new MySql();
        $sql = "select u_id,u_nick,u_type from t_user where u_id = '".$this->options['id']."'";
        $result = $db->getRow($sql);

        if(!$result){
            return $this->show(message::getJsonMsgStruct('1002'));
        }

        $info = array(
            'id' => $result['u_id'],
            'nick' => $result['u_nick'],
            'type' => $result['u_type']
        );
        return $this->show(message::getJsonMsgStruct('1001',$info));
    }
 }