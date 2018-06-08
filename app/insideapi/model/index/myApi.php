<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/12
 * Time: 10:07
 */
class myApi extends guest{

    function __construct($options = array(), $power = array()) {
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
        //已通过审核的APP
        $sql = "select da_name,da_app_key from t_develop_application where da_dp_id = '".$_SESSION['dp_id']."' and da_status = '2' order by da_createtime desc";
        $data = $db->getAll($sql);
        //已添加的API
        $sql = "select il_name,ip_app_key,ip_status,ip_createtime from t_interface_privilege as ip left join t_interface_list as il on ip.ip_api_id = il.il_id where ip_dp_id = '".$_SESSION['dp_id']."'";
        $result = $db->getAll($sql);

        foreach($data as $key=>$value){
            $data[$key]['message'] = '';
            foreach($result as $k=>$v){
                if($v['ip_status'] == 0){
                    $result[$k]['ip_status'] = '正常';
                }else if($v['ip_status'] == 1){
                    $result[$k]['ip_status'] = '冻结';
                }else{
                    $result[$k]['ip_status'] = '注销';
                }
                if($value['da_app_key'] == $v['ip_app_key']){
                    $data[$key]['message'].='<tr class="odd" role="row">
                                                <td>'.$v['il_name'].'</td>
                                                <td>'.$v['ip_app_key'].'</td>
                                                <td>'.$result[$k]['ip_status'].'</td>
                                                <td>'.$v['ip_createtime'].'</td>
                                            </tr>';
                }
            }
        }
        if(empty($data)){
            $param['check'] = '1001';
        }else{
            $param['check'] = '1002';
        }
        $this->setReplaceData($param);
        $this->setLoopData('data',$data);
        $this->setTempAndData('myApi/myApi');
        $this->show();
    }
}