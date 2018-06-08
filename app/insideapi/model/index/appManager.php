<?php

class appManager extends guest {

    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
    }

    function run() {

		$this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');
        //校验是否登录
        if(!isset($_SESSION['userID'])){
            header('Location:http://'.INSIDEAPI.'/index/console');
            exit;
        }

        $db = new MySql();

        //开发者申请的所有应用
        $sql = "select * from t_develop_application where da_dp_id = '".$_SESSION['dp_id']."' and da_status < 4 order by da_status asc,da_createtime desc";
        $data = $db->getAll($sql);
        if(empty($data)){
            $check = '1001';
        }else{
            $check = '1002';
        }
        foreach($data as $key=>$value){
            $data[$key]['add_api'] = '';
            if($value['da_class'] == 1){
                $data[$key]['da_class_text'] = 'web网页应用';
            }elseif($value['da_class'] == 2){
                $data[$key]['da_class_text'] = 'app移动应用';
            }else{
                $data[$key]['da_class_text'] = '硬件接入应用';
            }

            if($value['da_status'] == 1){
                $data[$key]['da_status_text'] = '<span class="font-green">审核中</span>';
            }elseif($value['da_status'] == 2){
                $data[$key]['da_status_text'] = '<span class="font-blue">审核通过</span>';
                $data[$key]['add_api'] = '<a href="/index/addApi?id='.$value['da_id']. '"class="font-blue margin-right-10">添加API</a>';
            }else{
                $data[$key]['da_status_text'] = '<span class="font-red">审核失败</span>';
            }
            $data[$key]['da_tel'] = F::hidtel($value['da_tel']);
        }
        //开发者资料
        $sql = "select u_logo,dp_contact,dp_tel,dp_email from t_user left join t_develop_partner on u_id = dp_uid where u_id = '".$_SESSION['userID']."'";
        $develop_partner = $db->getRow($sql);
        //检查资料的完整度
        $dp = $develop_partner;
        foreach($dp as $key=>$value){
            if($key != 'u_logo'){
                if($value == ''){
                    unset($dp[$key]);
                }
            }
        }
        $count = count($dp)-1;
        if($count == 3){
            $develop_partner['count'] = '100';
        }else{
            $develop_partner['count'] = $count*30;
        }
        //判断头像是否为空，如果为空就给默认头像
        if($develop_partner['u_logo'] == ''){
            $develop_partner['u_logo'] = 'https://image.999qf.cn/v1/tfs/T1.ddvB7DT1RCvBVdK.jpg';
        }
        $this->setReplaceData($develop_partner);

        //统计应用个数
        $applicationCount = count($data);

        //统计接口总是
        $apisql = "select count(*) from t_interface_privilege WHERE ip_dp_id = '".$_SESSION['dp_id']."'";
        $apicount = $db->getField($apisql);

        $temp = array(
            'applicationCount' => $applicationCount,
            'apicount' => $apicount,
            'check' => $check,
        );

        $this->setLoopData('data',$data);
        $this->setReplaceData($temp);
        $this->setTempAndData('appManager/appManager');
        $this->show();
    }


}