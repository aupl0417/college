<?php
/**
 * 查看企业会员信息
 * Created by PhpStorm.
 * User: jojojing
 * Date: 2016/5/9
 * Time: 10:07
 */

class viewCompany extends worker {
    function __construct($options) {
        parent::__construct($options, [60101]);
    }

    function run() {
        if(!isset($this->options['id'])){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;
        }
        $db = new MySql();
        $attrib = new attrib();
        $country = $db->getAll("select coun_id as id,coun_name as `name` from t_country ORDER BY coun_id=37 DESC");
        $countryType = array_column($country,'name','id');
        $user = new user();
        $userInfo = $user->getFulluserInfo($this->options['id']);
        $areaUnionFull = $attrib->getFullArea($userInfo['u_comArea'],0);
        $indFull = $attrib->getFullInd($userInfo['u_comIndid'],0);
        if($userInfo['u_type'] == 1){
            $sql = "select at_key as id, at_value as name from t_attrib where at_type = 4";
            $comTypes = $db->getAll($sql);
            $companyTypes = F::getAttrs(4);

            $data = [
                'id'       => $userInfo['u_id'],
                'username' => $userInfo['u_nick'],
                'usertype' => $userInfo['u_type'],
                'realname' => $userInfo['u_name'],
                'mainIndustry' => $userInfo['u_comMainIndustry'],
                'auth' => $userInfo['u_auth'],
                'phone' => empty($userInfo['u_tel']) ?  '****' : F::hidtel($userInfo['u_tel']),
                'createtime' => $userInfo['u_createTime'],
                'email' => empty($userInfo['u_email']) ? '****' : F::hidEmail($userInfo['u_email']),
                'comArea' => $userInfo['u_comArea'],
                'shopUrl' => $userInfo['u_shopUrl'],
                'userTypes' => array('0' => '个人用户','1' =>'企业用户'),
                'authInfo' => $userInfo['userAuthInfo'],
                'comAddress' => $userInfo['u_comAddress'],
                'comLeadName' => $userInfo['u_comLeadName'],
                'country' =>$country,
                'legalName' => empty($userInfo['u_comLegalName']) ? '****' : substr_replace($userInfo['u_comLegalName'],'*',0,3),
                'licenseCode' => empty($userInfo['u_comLicenseCode']) ? '****' : substr_replace($userInfo['u_comLicenseCode'],'********',6,8),
                'companyName' => $userInfo['u_companyName'],
                'quit'    => $userInfo['u_isQuit'],
                'companyType' => $userInfo['u_companyType'],
                'comTypes'	=> $companyTypes,
                'compType'	=> $comTypes,
                'isBranch'	=> $userInfo['u_isBranch'],
                'isBranchType'	=> array('0' => '否','1' => '是'),
                'companyThree'	=> $userInfo['u_companyThree'],
                'companyThreeType' => array('0' => '否','1' => '是'),
                'comOrgCode' => !empty($userInfo['u_comOrgCode']) ? $userInfo['u_comOrgCode'] : '',
                'comTaxCode' => !empty($userInfo['u_comTaxCode']) ? $userInfo['u_comTaxCode'] : '',
                'countryType' => $countryType,
                'specialAllow' => $userInfo['u_specialAllow'],
                'areaUnionFull' => $areaUnionFull,
                'indFull' => $indFull,
                'indId' => !empty($userInfo['u_comIndid']) ? $userInfo['u_comIndid'] : '',
            ];

        }
        $info = array(
            'tempId'		=> 'temp_'.F::getGID(),
            'id'            => $this->options['id'],
            'jsData'        => json_encode($data),
        );


        $this->setReplaceData($info);
        $this->setTempAndData();
        $this->show();
    }
}
