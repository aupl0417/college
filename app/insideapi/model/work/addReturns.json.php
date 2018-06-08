<?php

class addReturns_json extends guest {
    private $db;
    function __construct($options) {
        parent::__construct($options, [8]);
        $this->db = new MySql();
    }

    function run() {
        $options = $this->options;

        if(!isset($_SESSION['userID'])){
            echo '<script>alert("请先登录！",location.href="http://'.INSIDEAPI.'")</script>';
        }

        foreach($options['irf_as_name'] as $key=>$value){
            if($value != '' || $options['irf_db_field'][$key] != '' || $options['irf_type'][$key] != '' || $options['irf_description'][$key] != ''){
                if($value == ''){
                    $this->show(message::getJsonMsgStruct(1002,'第'.($key+1).'条数据的字段别名项没有填写，请填写！'));
                    exit;
                }
                if($options['irf_db_field'][$key] == ''){
                    $this->show(message::getJsonMsgStruct(1002,'第'.($key+1).'条数据的数据库字段名称项没有填写，请填写！'));
                    exit;
                }
                if($options['irf_type'][$key] == ''){
                    $this->show(message::getJsonMsgStruct(1002,'第'.($key+1).'条数据的字段格式项没有选择，请选择！'));
                    exit;
                }
                if($options['irf_description'][$key] == ''){
                    $this->show(message::getJsonMsgStruct(1002,'第'.($key+1).'条数据的描述项没有填写，请填写！'));
                    exit;
                }
                $data[] = array( $options['irf_il_id'],$options['irf_db_field'][$key],$value,$options['irf_type'][$key],$options['irf_lenght'][$key],$options['irf_description'][$key],$options['irf_example'][$key]
                );
            }else{
                if(empty($data)){
                    $this->show(message::getJsonMsgStruct(1002,'内容不能为空，请填写内容后再提交！'));
                    exit;
                }
            }
        }
        foreach($data as $key=>$value){
            if($value[1] == '' || $value[2] == ''){
                unset($data[$key]);
            }
        }
        if(empty($data)){
            $this->show(message::getJsonMsgStruct(1002,'添加失败'));
        }else{
            $p = array('irf_il_id','irf_db_field','irf_as_name','irf_type','irf_lenght','irf_description','irf_example'
            );

            if($this->db->inserts('t_interface_response_field',$p,$data)){
                $this->show(message::getJsonMsgStruct(1001,'添加成功'));
            }
            else{
                $this->show(message::getJsonMsgStruct(1002,'添加失败'));
            }
        }

    }

}
