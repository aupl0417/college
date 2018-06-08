<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/24
 * Time: 11:48
 */
 class userTypeChange extends worker{
    function __construct($options) {
        parent::__construct($options, [609]);
    }

    function run() {
        $this->options['id'] = isset($this->options['id']) ? $this->options['id'] : '' ;
        $db = new MySql();
        $sql = "select u_nick,u_type from t_user where u_id = ".$this->options['id'];
        $result = $db->getRow($sql);

        $data = array(
            'code'          => 609,
            'tempId'		=> 'temp_'.F::getGID(),
            'nick'        => $result['u_nick'],
            'type'        => $result['u_type'],
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
 }