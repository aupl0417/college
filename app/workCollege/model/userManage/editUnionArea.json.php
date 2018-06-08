<?php
/**
 * 行业编辑
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/6
 * Time: 19:10
 */
class editUnionArea_json extends worker {
    function __construct($options) {
        parent::__construct($options, [6011049]);
    }
    function run() {
        $options = $this->options;
        $id = isset($options['userid']) ? $options['userid'] : "";
        $areaId = isset($options['areaId']) ? $options['areaId'] : "";
        if ($id == "" || $areaId == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        $db = new MySql();
        $sql = "select * from t_user_company where u_id = '".$id."'";
        $result = $db->getRow($sql);
        if(!$result){
            $this->show(message::getJsonMsgStruct('1002','参数错误!'));
            exit;
        }

        if(strlen($areaId) == 12 && substr($areaId,9,3) != '000'){
            $sql = "select * from t_areaex where ae_code = '".$areaId."'";
        }else{
            $sql = "select * from t_area where a_code = '".$areaId."'";
        }
        $result = $db->getRow($sql);
        if(!$result){
            $this->show(message::getJsonMsgStruct('1002','操作失败！'));
            exit;
        }

        $arr = array( 'u_comArea' => $areaId);
        $wheres = " u_id = '".$id."'";
        $result = $db->update('t_user_company',$arr,$wheres);
        if(!$result){
            $this->show(message::getJsonMsgStruct('1002','操作失败！'));
            exit;
        }
        $this->show(message::getJsonMsgStruct('1001','操作成功！'));
    }
}