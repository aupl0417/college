<?php
/**
 * 查看个人会员信息
 * Created by PhpStorm.
 * User: jojojing
 * Date: 2016/5/9
 * Time: 10:07
 */
class viewPerson extends worker{
    function __construct($options) {
        parent::__construct($options,[60101]);
    }

    function run() {
        $this->options['id'] = isset($this->options['id']) ? $this->options['id'] : '';
        if($this->options['id'] == ''){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        $db = new MySql();
        $country = $db->getAll("select coun_id as id,coun_name as `name` from t_country ORDER BY coun_id=37 DESC");
        $countryType = array_column($country,'name','id');
        $user = new user();
        $attrib = new attrib();
        $userInfo = $user->getFulluserInfo($this->options['id']);
        $politicalType = F::getAttrs(18);
        $education = F::getAttrs(22);
        $indFull = $attrib->getFullInd($userInfo['u_indId'],0);
        $areaFull = $attrib->getFullArea($userInfo['u_area'],0);
        if ($userInfo['u_type'] == 0) {
            $data = [
                'id'            => $userInfo['u_id'],
                'username'      => $userInfo['u_nick'],
                'usertype'      => $userInfo['u_type'],
                'realname'      => empty($userInfo['u_name']) ? '****' : substr_replace($userInfo['u_name'],'*',0,3),
                'certType'      => $userInfo['u_certType'],
                'sex'           => $userInfo['u_sex'],
                'certnum'       => empty($userInfo['u_certNum']) ? '****' : substr_replace($userInfo['u_certNum'], "********", 6, 8),
                'auth'          => $userInfo['u_auth'],
                'phone'         => empty($userInfo['u_tel']) ? '****' : F::hidtel($userInfo['u_tel']),
                'createtime'    => $userInfo['u_createTime'],
                'tel'           => $userInfo['u_otherTel'],
                'postage'       => $userInfo['u_postage'],
                'address'       => $userInfo['u_address'],
                'area'          => $userInfo['u_area'],
                'indId'         => $userInfo['u_indId'],
                'email'         => empty($userInfo['u_email']) ? '****' : F::hidEmail($userInfo['u_email']),
                'shopUrl'       => $userInfo['u_shopUrl'],
                'qq'            => $userInfo['u_qq'],
                'userTypes'     => array('0' => '个人用户','1' =>'企业用户'),
                'birth'         => $userInfo['u_birth'],
                'nation'        => $userInfo['u_country'],
                'authInfo'      => $userInfo['userAuthInfo'],
                'country'       => $country,
                'quit'          => $userInfo['u_isQuit'],
                'eduID'         => $userInfo['u_eduID'],
                'political'     => $userInfo['u_political'],
                'career'        => $userInfo['u_career'],
                'u_native'      => $userInfo['u_native'],
                'countryType'   => $countryType,
                'politicalType' => $politicalType,
                'education'     => $education,
                'indFull'       => $indFull,
                'areaFull'      => $areaFull,
                'indId'         => $userInfo['u_indId'],
            ];
        }
        else if($userInfo['u_type'] == 1){

        }

        $info = array(
            'tempId'		=> 'temp_'.F::getGID(),
            'jsData'        => json_encode($data),
        );
        $this->setReplaceData($info);
        $this->setTempAndData();
        $this->show();
    }
}
