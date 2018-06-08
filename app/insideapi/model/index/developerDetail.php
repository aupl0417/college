<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/11
 * Time: 15:33
 */
class developerDetail extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

        $this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');

        $db = new MySql();
        //校验是否登录
        if(!isset($_SESSION['userID'])){
            header('Location: http://'.INSIDEAPI.'/index/console');
            exit;
        }
        $sql = "select u_nick,u_type,u_level,u_name,u_companyName,u_tel,u_email from t_user where u_id = '".$_SESSION['userID']."'";
        $data = $db->getRow($sql);
        if($data['u_type'] == 0){
            $data['u_type'] = '个人';
            $data['name'] = $data['u_name'];
        }else{
            $data['u_type'] = '企业';
            $data['name'] = $data['u_companyName'];
        }
        if($data['u_level'] == 1){
            $data['u_level'] = '消费商';
        }elseif($data['u_level'] == 2){
            $data['u_level'] = '银钻用户';
        }elseif($data['u_level'] == 3){
            $data['u_level'] = '金钻用户';
        }else{
            $data['u_level'] = '铂钻用户';
        }
        $this->setReplaceData($data);
        $this->setTempAndData('developerDetail/developerDetail');
        $this->show();
    }


}