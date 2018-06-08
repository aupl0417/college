<?php
/**
 * 行业编辑
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/6
 * Time: 19:11
 */
class editUnionArea extends worker {
    function __construct($options) {
        parent::__construct($options, [6011049]);
    }
    function run() {
        $options = $this->options;
        $id = isset($options['id']) ? $options['id'] : "";
        $comAreaId = isset($options['area']) ? $options['area'] : "";
        if($id == "" || $comAreaId == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        $data = array(
            'comArea' => $comAreaId,
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