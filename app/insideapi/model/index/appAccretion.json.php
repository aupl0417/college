<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/1
 * Time: 14:51
 */
class appAccretion_json extends guest {
    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        //判断是否已经登录
        if(!isset($_SESSION['userID'])){
            header('Location:http://'.INSIDEAPI.'/index/console');
            exit;
        }

        $options = $this->options;
        $db = new MySql();

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

        $sql = "select dp_id from t_develop_partner where dp_uid = '".$_SESSION['userID']."'";
        $dp_id = $db->getRow($sql);
        $p = array(
            'da_name'            => $options['da_name'],
            'da_platform'       => implode(',',$options['da_platform']),
            'da_class'          => $options['da_class'],
            'da_icon'           => $options['da_icon'],
            'da_briefing'       => $options['da_briefing'],
            'da_scene'          => $options['da_scene'],
            'da_screenshot'     => $options['da_screenshot'],
            'da_domain'         => $options['da_domain'],
            'da_server_ip'      => $options['da_server_ip'],
            'da_callback'       => $options['da_callback'],
//            'da_cancel_callback'   => $options['da_cancel_callback'],
            'da_dp_id'          => $dp_id['dp_id'],    //申请人ID
            'da_status'         => '1',     //提交审核状态
            'da_app_key'        => strtoupper(substr(md5(time().'!@#$%^&^*()_&_+%$15asd158arfxz-*/,SL3'),8,16)),
            'da_secret_key'    => strtoupper(md5(time().'!@#$%^&^*()_&_+%$15asd158arfxz')),
            'da_contact'        => $options['da_contact'],
            'da_tel'            => $options['da_tel'],
            'da_email'          => $options['da_email'],
            'da_createtime'    => F::mytime()
        );
        if($db->insert('t_develop_application',$p)){
            $this->show(message::getJsonMsgStruct('1001','提交成功'));
            exit;
        }else{
            $this->show(message::getJsonMsgStruct('1002','提交失败'));
            exit;
        }
    }


}