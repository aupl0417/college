<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/11
 * Time: 16:16
 */
class addApi extends guest {

    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
    }

    function run() {
        $this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');
        if(!isset($_SESSION['userID'])){
            header('Location: http://'.INSIDEAPI.'/index/console');
            exit;
        }
        $db = new MySql();
        //查找应用的信息
        $sql = "select da_app_key,da_dp_id from t_develop_application where da_id = '".$this->options['id']."'";
        $app_messages = $db->getRow($sql);
        //查找所有API的分类
        $sql = "select * from t_interface_category";
        $category = $db->getAll($sql);

        $this->setReplaceData($app_messages);
        $this->setLoopData('category',$category);
        $this->setTempAndData('addApi/addApi');
        $this->show();
    }


}