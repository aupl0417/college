<?php
/**
 * 独立查看个人【手机号，身份证，邮箱，真是姓名】，企业【手机号，邮箱，法人姓名，营业执照编号】
 * Created by PhpStorm.
 * User: JoJoJing
 * Date: 2016/8/29
 * Time: 11:40
 */
class hightView extends worker{
    function __construct($options) {
        $type = isset($options['type']) ? $options['type'] : 1;
        $powers = array(
            1 => 6010101,
            2 => 6010102,
            3 => 6010103,
            4 => 6010104,
            5 => 6010105,
            6 => 6010106,
            7 => 6010107,
            8 => 6010108,
        );
        parent::__construct($options, [$powers[$type]]);
    }

    function run(){
        $id = (isset($this->options['id']) && strlen($this->options['id']) == 32) ? $this->options['id'] : '';  //会员ID
        $type = isset($this->options['type']) ? $this->options['type'] : 1;
        if(empty($id)){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        switch($type){
            case 1:
                $fields = 'u_tel';
                $data['msg'] = '联系手机';
                break;
            case 2:
                $fields = 'u_email';
                $data['msg'] = '邮箱';
                break;
            case 3:
                $fields = 'u_certNum';
                $data['msg'] = '身份证号码';
                break;
            case 4;
                $fields = 'u_name';
                $data['msg'] = '真实姓名';
                break;
            case 5:
                $fields = 'u_comLegalName';
                $data['msg'] = '法人姓名';
                break;
            case 6:
                $fields = 'u_comLicenseCode';
                $data['msg'] = '营业执照编号';
                break;
            case 7:
                $fields = 'u_email';
                $data['msg'] = '邮箱';
                break;
            case 8:
                $fields = 'u_tel';
                $data['msg'] = '联系手机';
                break;
        }

        $db = new MySql();
        if($type == 3){
            $sql = "select {$fields} from t_user_person where u_id = '".$id."'";
        }elseif($type == 5 || $type == 6){
            $sql = "select {$fields} from t_user_company where u_id = '".$id."'";
        }else{
            $sql = "select {$fields} from t_user where u_id = '".$id."'";
        }

        $info = $db->getField($sql);
        $info = (isset($info) && !empty($info)) ? $info : '暂无填写此信息';

        $data['info'] = $info;

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}