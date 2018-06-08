<?php
/**
 * 行业编辑
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/6
 * Time: 19:10
 */
class editIndustry_json extends worker {
    function __construct($options) {
        $db = new MySql();
        $id = isset($options['userid']) ? $options['userid'] : "";
        if($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $sql = "select u_type from t_user where u_id = '".$id."'";
        $res = $db->getField($sql);
        $powers = array( '0' => '6011022','1' => '6011045');
        parent::__construct($options, [$powers[$res]]);
    }
    function run() {
        $options = $this->options;
        $id = isset($options['userid']) ? $options['userid'] : "";
        $indId = isset($options['indId']) ? $options['indId'] : "";
        if ($id == "" || $indId == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        $db = new MySql();
        $sql = "select * from t_user where u_id = '".$id."'";
        $result = $db->getRow($sql);
        if(!$result){
            $this->show(message::getJsonMsgStruct('1002','参数错误!'));
            exit;
        }
        $type = $result['u_type'];

        $sql = "select * from t_industry where ind_code = '".$indId."'";
        $result = $db->getRow($sql);
        if(!$result){
            $this->show(message::getJsonMsgStruct('1002','操作失败！'));
            exit;
        }
        $wheres = " u_id = '".$id."'";
        if($type == 0){
            $arr = array( 'u_indId' => $indId);
            $result = $db->update('t_user_person',$arr,$wheres);
        }else{
            $arr = array( 'u_comIndid' => $indId);
            $result = $db->update('t_user_company',$arr,$wheres);
        }
        if(!$result){
            $this->show(message::getJsonMsgStruct('1002','操作失败！'));
            exit;
        }
        $this->show(message::getJsonMsgStruct('1001','操作成功！'));

    }
}