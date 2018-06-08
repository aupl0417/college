<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/7
 * Time: 14:37
 */
class beDeveloper_json extends guest {
    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        $options = $this->options;
        $db = new MySql();

        //判断验证码是否正确
        $user = new user();
        if (!$user->uniqueUserInfo(5, $options['code'], '', 'code')) {
            $this->show(message::getJsonMsgStruct('1004','验证码错误')); //验证码错误
            exit;
        }
        //判断是否同意协议
        if(@$options['agree'] != 1){
            $this->show(message::getJsonMsgStruct('1003','请同意本平台所列出的协议'));
            exit;
        }
        //判断邮箱格式
        if(!F::isEmail($options['dp_email'])){
            $this->show(message::getJsonMsgStruct('1005','邮箱格式错误'));
            exit;
        }

        //身份的验证
        $user = new user($db);
        $userInfo = $user->getFullUserInfo($_SESSION['userID']);

        //检查是否实名认证
        if($userInfo['u_type'] == 0){
            if($userInfo['userAuthInfo']['person']['authed'] != 1){
                $this->show(message::getJsonMsgStruct('1007','个人身份还没有在大唐天下ERP系统认证，请先到大唐天下ERP系统进行个人身份认证'));
                exit;
            }
        }else if($userInfo['u_type'] == 1){
            if(!$userInfo['userAuthInfo']['company']['authed']){
                $this->show(message::getJsonMsgStruct('1006','企业还没有在大唐天下ERP系统认证，请先到大唐天下ERP系统进行企业认证'));
                exit;
            }
        }else{
            $this->unlockFormToken($this->options['_posttoken']);
            $this->show(message::getJsonMsgStruct('1002', '用户错误'));
            exit;
        }

        $p = array(
            'dp_contact'        => $options['dp_contact'],
            'dp_tel'            => $options['dp_tel'],
            'dp_email'          => $options['dp_email'],
            'dp_uid'            => $_SESSION['userID'],
            'dp_createtime'    => F::mytime(),
            'dp_status'        => '0'
        );
        $url= 'http://'.INSIDEAPI.'/index/console';//申请成功后跳转到控制台页面

        //判断当前用户是否申请被拒绝，再次申请
        $sql = "select * from t_develop_partner where dp_uid = '".$_SESSION['userID']."'";
        $data = $db->getRow($sql);
        $data['dp_status'] = '';
        if($data['dp_status'] == 4){
            $result = $db->update('t_develop_partner',$p,"dp_uid = '".$_SESSION['userID']."'");
            if($result !== false){
                $this->show(message::getJsonMsgStruct('1001',$url));
                exit;
            }else{
                $this->show(message::getJsonMsgStruct('1002','提交失败'));
            }
        }else{
            if($db->insert('t_develop_partner',$p)){
                $this->show(message::getJsonMsgStruct('1001',$url));
                exit;
            }else{
                $this->show(message::getJsonMsgStruct('1002','提交失败'));
                exit;
            }
        }
    }
}