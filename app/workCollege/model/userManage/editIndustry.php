<?php
/**
 * 行业编辑
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/6
 * Time: 19:11
 */
class editIndustry extends worker {
    function __construct($options) {
        $db = new MySql();
        $id = isset($options['id']) ? $options['id'] : "";
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
        $id = isset($options['id']) ? $options['id'] : "";
        $indId = isset($options['indId']) ? $options['indId'] : "";
        if($id == "" || $indId == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        $data = array(
            'indId' => $indId,
            'id' => $id,
        );

        $info = array(
            'tempId'		=> 'temp_'.F::getGID(),
            'jsData' 		=> json_encode($data),
        );

        $this->setReplaceData($info);
        $this->setTempAndData();
        $this->show();
    }
}