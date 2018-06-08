<?php

class appDetails extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

		$this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');
        //校验是否登录
        if(!isset($_SESSION['userID'])){
            header('Location: http://'.INSIDEAPI.'/index/console');
            exit;
        }

        $db = new MySql();
        $sql = "select * from t_develop_application where da_id = '".$this->options['id']."'";
        $data = $db->getRow($sql);
        if($data['da_status'] == 1){
            $data['da_status'] = '<small class="font-green">审核中</small>';
        }elseif($data['da_status'] == 2){
            $data['da_status'] = '<small class="font-blue">审核通过</small>';
        }else{
            $data['da_status'] = '<small class="font-red">审核失败</small>';
        }
        if($data['da_class'] == 1){
            $data['da_class'] = 'web网页应用';
        }elseif($data['da_class'] == 2){
            $data['da_class'] = 'app移动应用';
        }else{
            $data['da_class'] = '硬件接入应用';
        }
        if(empty($data['da_screenshot'])){
            $data['da_screenshot'] = 'https://image.999qf.cn/v1/tfs/T1.ddvB7DT1RCvBVdK.jpg';
        }
        $this->setReplaceData($data);
        $this->setTempAndData('appDetails/appDetails');
        $this->show();
    }


}