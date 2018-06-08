<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/11
 * Time: 17:19
 */
class selectApi_json extends guest {
    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        $db = new MySql();

//        //搜索t_interface_privilege表已经由此应用添加的api
//        $sql = "select ip_api_id,ip_app_key from t_interface_privilege where ip_app_key = '".$this->options['key']."' and ip_dp_id = '".$_SESSION['dp_id']."'";
//        $ip = $db->getAll($sql);

        //搜索对应分类下的API
        $sql = "select il_id,il_name from t_interface_list where il_ic_id = '".$this->options['class']."' and il_id not in (select ip_api_id from t_interface_privilege where ip_app_key = '".$this->options['key']."' and ip_dp_id = '".$_SESSION['dp_id']."')";
        $data = $db->getAll($sql);

        //定义变量存储html代码
        $message = '';

        //去除已经在该应用添加的api
            foreach($data as $key=>$value){
                    $message .= '<tr><td>
                <input type="text" style="border-left:0px;border-top:0px;border-right:0px;border-bottom:1px " id="demo1" value="'.$value['il_name'].'">
            </td><td><input type="checkbox" name="api_id['.$value['il_id'].']" value="'.$value['il_id'].'"></td></tr>';

            }

        $info = array(
            'message'   => $message,
            'id'         => $this->options['class']
        );
        if($data){
            $this->show(message::getJsonMsgStruct('1001',$info));
            exit;
        }else{
            $info = array(
                'message'   => '<tr><td align="center" style="font-size: 24px">没有相应的API或者您已经在该应用添加了该API</td></tr>',
                'id'        => $this->options['class']
            );
            $this->show(message::getJsonMsgStruct('1002',$info));
            exit;
        }
    }


}