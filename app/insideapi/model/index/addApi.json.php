<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/11
 * Time: 19:12
 */

//接口的添加
class addApi_json extends guest {
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
    }

    function run() {
        $db = new MySql();
        if(!isset($_SESSION['userID'])){
            header('Location:http://'.INSIDEAPI.'/index/console');
            exit;
        }
        $options = $this->options;
        $p = array();
        $l = array();
        foreach($options['api_id'] as $key=>$value){
            //插入t_interface_privilege表的数据
            $p[] = array($options['app_key'],$options['dp_id'],$value,F::mytime(), 0);
            //插入t_interface_log表的数据
            $l[] = array($value,$options['dp_id'],$options['app_key'],F::mytime(),0);
        }
        //插入t_interface_privilege表的数据
        $paramIP = array('ip_app_key','ip_dp_id','ip_api_id','ip_createtime','ip_status');
        $data = $db->inserts('t_interface_privilege',$paramIP,$p);

        //插入t_interface_log表的数据
        $paramIL = array('il_api_id','il_partner_id','il_app_key','il_create_time','il_request_times');
        $result = $db->inserts('t_interface_log',$paramIL,$l);

        //数据插入成功后跳转的地址
        $url = 'http://'.INSIDEAPI.'/index/appManager';

        if($data && $result){
            $this->show(message::getJsonMsgStruct('1001',$url));
            exit;
        }else{
            $this->show(message::getJsonMsgStruct('1002','接口添加失败'));
            exit;
        }
    }
}