<?php

class addRequest_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }

        $options = $this->options;
        if(count($options['iqf_il_required']) != count($options['iqf_il_name'])){
            $this->show(message::getJsonMsgStruct(1002,'是否必填项只能选一个，不能多选或不选！'));
            exit;
        }
        foreach($options['iqf_il_name'] as $key=>$value){
            if($value != '' || $options['iqf_il_type'][$key] != '' || $options['iqf_il_description'][$key] != ''){
                if($value == ''){
                    $this->show(message::getJsonMsgStruct(1002,'第'.($key+1).'条数据的字段名称项没有填写，请填写！'));
                    exit;
                }
                if($options['iqf_il_type'][$key] == ''){
                    $this->show(message::getJsonMsgStruct(1002,'第'.($key+1).'条数据的字段格式项没有选择，请选择！'));
                    exit;
                }
                if($options['iqf_il_required'][$key] == ''){
                    $this->show(message::getJsonMsgStruct(1002,'第'.($key+1).'条数据的字段是否必填项没有选择，请选择！'));
                    exit;
                }
                if($options['iqf_il_description'][$key] == ''){
                    $this->show(message::getJsonMsgStruct(1002,'第'.($key+1).'条数据的描述项没有填写，请填写！'));
                    exit;
                }
                $data[] = array( $options['iqf_id'],$value, 2,$options['iqf_il_type'][$key],$options['iqf_il_required'][$key],$options['iqf_il_default'][$key],$options['iqf_il_description'][$key],
                );
            }else{
                if(empty($data)){
                    $this->show(message::getJsonMsgStruct(1002,'内容不能为空，请填写内容后再提交！'));
                    exit;
                }
            }
        }
        foreach($data as $key=>$value){
           if($value[1] == ''){
               unset($data[$key]);
           }
        }
        if(empty($data)){
            $this->show(message::getJsonMsgStruct(1002,'添加失败'));
        }else{
            $p = array('iqf_il_id','iqf_il_name','iqf_il_is_public','iqf_il_type','iqf_il_required','iqf_il_default','iqf_il_description',
            );
            if($this->db->inserts('t_interface_request_field',$p,$data)){
                $this->show(message::getJsonMsgStruct(1001,'添加成功'));
            }
            else{
                $this->show(message::getJsonMsgStruct(1002,'添加失败'));
            }
        }

    }

}
