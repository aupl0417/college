<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/9
 * Time: 14:11
 */
class appDetails_json extends guest {
    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        $db = new MySql();
        //重置da_secret_key
        $secret_key = strtoupper(md5(time().'!@#$%^&^*()_&_+%$15asd158arfxz'));
        $result = $db->update('t_develop_application',array('da_secret_key'=>$secret_key),"da_id = '".$this->options['da_id']."'");
        $info = array(
          'secret'  =>  $secret_key
        );
        if($result !== false){
            $this->show(message::getJsonMsgStruct('1001',$info));
            exit;
        }else{
            $this->show(message::getJsonMsgStruct('1002','重置失败'));
            exit;
        }
    }


}