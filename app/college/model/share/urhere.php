<?php

class urhere extends member {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        $db = new MySql();
//         dump($this->options);die;
        $code = isset($this->options['code']) ? $this->options['code']:"";
//         dump($code);die;
        if($code == "" || !F::fmtNum($code)){
            $this->show(message::getJsonMsgStruct('1002', "参数错误")); //登录失败
            exit;
        }
		$webUrl = "https://".WWWURL;
		$sql = "SELECT * FROM `tang_power` WHERE p_id = SUBSTR('".$code."', 1, LENGTH(p_id)) ORDER BY p_fid asc";
        $data = $db->getAll($sql);
//         dump($data);die;
        $_urhere = '<li> <i class="fa fa-home"></i> <a href="'.$webUrl.'" id="head">首页</a> <i class="fa fa-angle-right"></i> </li>';
        $_count = count($data) - 1;
        foreach ($data as $k=>$val){
            if($k == $_count){
                $_urhere .= '<li> '.$val['p_name'].'</li>';
            }else{
                if($val['p_url'] == "u"){
                   $val['p_url'] = "";
                }
                $_class = $val['p_fid'] == 0 ? '' : ' class="ajaxify"';
                $_urhere .= '<li> <a href="'.$val['p_url'].'"'.$_class.'>'.$val['p_name'].'</a> <i class="fa fa-angle-right"></i></li>';
            }
        }
		
// 		echo $_urhere;
        /* $data = array(
            'webUrl' => $webUrl,
        ); */
        $this->show(message::getJsonMsgStruct('1001', $_urhere)); //登录成功
    }

}
