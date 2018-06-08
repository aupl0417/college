<?php
/**
 * 会员身份转换查看
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/11
 * Time: 10:14
 */
class editUserType extends worker {
    function __construct($options) {
        parent::__construct($options, [609]);
    }
    function run() {
        $this->options['id'] = isset($this->options['id']) ? $this->options['id'] : '' ;
        $db = new MySql();
        $sql = "select u_id,u_nick,u_type from t_user where u_id = '".$this->options['id']."'";
        $result = $db->getRow($sql);

        if(!$result){
            $this->show(message::getJsonMsgStruct('1002'));
            exit;
        }

        $data = array(
            'id' => $result['u_id'],
            'nick' => $result['u_nick'],
            'type' => $result['u_type']
        );

        $info = array(
            'tempId'		=> 'temp_'.F::getGID(),
            'jsData' 		=> json_encode($data),
        );

        $this->setReplaceData($info);
        $this->setTempAndData();
        $this->show();
    }
}