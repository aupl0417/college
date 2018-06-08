<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/9
 * Time: 15:35
 */
class editDetails_json extends guest {
    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        $db = new MySql();
        $options = $this->options;

        //验证绑定域名的格式是否正确
        $da_domain = explode("，",$options['da_domain']);
        foreach($da_domain as $value){
            if(!preg_match('/^((https?|ftp|news):\/\/)?([a-z]([a-z0-9\-]*[\.。])+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\/[a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&]*)?)?(#[a-z][a-z0-9_]*)?$/',$value)){
                $this->show(message::getJsonMsgStruct('1006','请输入正确的绑定域名地址'));
                exit;
            }
        }
        //验证应用服务器的IP格式是否正确
        if(!preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/',$options['da_server_ip'])){
            $this->show(message::getJsonMsgStruct('1008','请输入正确的应用服务器IP'));
            exit;
        }
        //验证授权回调页的格式是否正确
        if(!preg_match('/^((https?|ftp|news):\/\/)?([a-z]([a-z0-9\-]*[\.。])+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\/[a-z0-9_\-\.~]+)*(\/([a-z0-9_\-\.]*)(\?[a-z0-9+_\-\.%=&]*)?)?(#[a-z][a-z0-9_]*)?$/',$options['da_callback'])){
            $this->show(message::getJsonMsgStruct('1005','请输入正确的授权回调页地址'));
            exit;
        }
        //验证运营者输入的姓名是否是中文
        if(!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$options['da_contact'])){
            $this->show(message::getJsonMsgStruct('1003','请输入中文姓名'));
            exit;
        }
        //验证运营者号码的格式是否正确
        if(!F::isPhone($options['da_tel'])){
            $this->show(message::getJsonMsgStruct('1007','请输入正确的手机号码'));
            exit;
        }
        //验证Email格式是否正确
        if(!F::isEmail($options['da_email'])){
            $this->show(message::getJsonMsgStruct('1004','请输入正确的邮箱'));
            exit;
        }
        //修改应用
        $p = array(
            'da_domain'         => $options['da_domain'],
            'da_server_ip'      => $options['da_server_ip'],
            'da_callback'       => $options['da_callback'],
            'da_briefing'       => $options['da_briefing'],
            'da_scene'          => $options['da_scene'],
            'da_screenshot'    => $options['da_screenshot'],
            'da_contact'        => $options['da_contact'],
            'da_tel'            => $options['da_tel'],
            'da_email'          => $options['da_email']
        );
        $result = $db->update('t_develop_application',$p,"da_id = '".$options['da_id']."'");
        $info = 'http://'.INSIDEAPI.'/index/appManager';
        if($result !== false){
            $this->show(message::getJsonMsgStruct('1001',$info));
            exit;
        }else{
            $this->show(message::getJsonMsgStruct('1002','修改失败'));
            exit;
        }
    }


}