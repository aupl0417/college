<?php
/**
 * 会员身份转换（个人转企业）
 * Created by PhpStorm.
 * User: JoJoJing
 * Date: 2016/9/3
 * Time: 14:19
 */
class uType extends worker{
    function __construct($options){
        parent::__construct($options, [60132]);
    }

    function run(){
        $options = $this->options;
        $id = (isset($options['id']) && strlen($options['id']) == 32) ? $options['id'] : '';
        if(empty($id)){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        //组织机构类型
        $organize = F::getAttrs(19);
        $organize = F::array2Options($organize);

        //获取当前用户信息
        $userInfo = apis::request('u/api/getUserByID.json', ['userID' => $id], true);
        if($userInfo['code'] != 1001){
            $userInfo['data'] = array();
        }
        $userInfo = $userInfo['data'];

        $data = array(
            'id'        => isset($userInfo['u_id']) ? $userInfo['u_id'] : '',
            'nick'      => isset($userInfo['u_nick']) ? $userInfo['u_nick'] : '',
            'type'      => isset($userInfo['u_type']) ? $userInfo['u_type'] : 0,
            'organize'  => $organize,
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}