<?php
/**
 * 查看会员信息
 * Created by PhpStorm.
 * User: jojojing
 * Date: 2016/5/9
 * Time: 10:07
 */

class view_json extends worker {
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
		$politicalType = F::getAttrs(18);
		$education = F::getAttrs(22);
		$indFull = $attrib->getFullInd($userInfo['u_indId'],0);
		$areaFull = $attrib->getFullArea($userInfo['u_area'],0);
		$areaUnionFull = $attrib->getFullArea($userInfo['u_comArea'],0);
        if ($userInfo['u_type'] == 0) {
            $info = [
					'username' => $userInfo['u_nick'],
					'usertype' => $userInfo['u_type'],
					'realname' => empty($userInfo['u_name']) ? '****' : substr_replace($userInfo['u_name'],'*',0,3),
                    'certType' => $userInfo['u_certType'],
					'sex' => $userInfo['u_sex'],
					'certnum' => empty($userInfo['u_certNum']) ? '****' : substr_replace($userInfo['u_certNum'], "********", 6, 8),
					'mainIndustry' => $userInfo['u_comMainIndustry'],
					'auth' => $userInfo['u_auth'],
					'phone' => empty($userInfo['u_tel']) ? '****' : F::hidtel($userInfo['u_tel']),
					'createtime' => $userInfo['u_createTime'],
					'tel' => $userInfo['u_otherTel'],
					'postage' => $userInfo['u_postage'],
					'address' => $userInfo['u_address'],
					'area' => $userInfo['u_area'],
					'indId' => $userInfo['u_indId'],
					'email' => empty($userInfo['u_email']) ? '****' : F::hidEmail($userInfo['u_email']),
					'shopUrl' => $userInfo['u_shopUrl'],
					'qq' => $userInfo['u_qq'],
					'userTypes' => array('0' => '个人用户','1' =>'企业用户'),
					'birth' => $userInfo['u_birth'],                
					'nation' => $userInfo['u_country'],                
					'authInfo' => $userInfo['userAuthInfo'],
					'country' =>$country,
                    'quit'    => $userInfo['u_isQuit'],
                    'eduID' => $userInfo['u_eduID'],
                    'political' => $userInfo['u_political'],
                    'career' => $userInfo['u_career'],
                    'u_native' => $userInfo['u_native'],
                    'comAddress' => $userInfo['u_comAddress'],
                    'countryType' => $countryType,
                    'politicalType' => $politicalType,
                    'education' => $education,
                    'specialAllow' => $userInfo['u_specialAllow'],
                    'indFull' => $indFull,
                    'areaFull' => $areaFull,
                    'areaUnionFull' => $areaUnionFull,
            ];
            $this->show(message::getJsonMsgStruct('1001', $info));//参数错误
            exit;
        }
        else if($userInfo['u_type'] == 1){
        	$sql = "select at_key as id, at_value as name from t_attrib where at_type = 4";
        	$comTypes = $db->getAll($sql);
        	$companyTypes = F::getAttrs(4);
        	
        	if ($userInfo['u_certType'] ==1){
        	    $typeName = "营业执照号";
        	}else{
        	    $typeName = "有效证件号";
        	}

            $info = [				
                    'typeName' => $typeName,
					'username' => $userInfo['u_nick'],
					'usertype' => $userInfo['u_type'],
					'realname' => $userInfo['u_name'],
					'sex' => $userInfo['u_sex'],
					'certnum' => $userInfo['u_certNum'],
					'mainIndustry' => $userInfo['u_comMainIndustry'],
					'auth' => $userInfo['u_auth'],
					'phone' => empty($userInfo['u_tel']) ?  '****' : F::hidtel($userInfo['u_tel']),
					'createtime' => $userInfo['u_createTime'],
					'tel' => $userInfo['u_otherTel'],
					'postage' => $userInfo['u_postage'],
					'address' => $userInfo['u_address'],
					'area' => $userInfo['u_area'],
					'indId' => $userInfo['u_indId'],
					'email' => empty($userInfo['u_email']) ? '****' : F::hidEmail($userInfo['u_email']),
					'comArea' => $userInfo['u_comArea'],
                    'nation' => $userInfo['u_country'],
					'shopUrl' => $userInfo['u_shopUrl'],
					'qq' => $userInfo['u_qq'],
					'userTypes' => array('0' => '个人用户','1' =>'企业用户'),
					'birth' => $userInfo['u_birth'],                
					'nation' => $userInfo['u_country'],                
					'authInfo' => $userInfo['userAuthInfo'],
                    'comAddress' => $userInfo['u_comAddress'],
                    'comLeadName' => $userInfo['u_comLeadName'],
					'country' =>$country,
					'legalName' => empty($userInfo['u_comLegalName']) ? '****' : substr_replace($userInfo['u_comLegalName'],'*',0,3),
					'licenseCode' => empty($userInfo['u_comLicenseCode']) ? '****' : substr_replace($userInfo['u_comLicenseCode'],'********',4,8),
					'indId' => $userInfo['u_indId'],
					'companyName' => $userInfo['u_companyName'],
                    'quit'    => $userInfo['u_isQuit'],
                    'companyType' => $userInfo['u_companyType'],
                    'comTypes'	=> $companyTypes,
                    'compType'	=> $comTypes,
                    'isBranch'	=> $userInfo['u_isBranch'],
                    'isBranchType'	=> array('0' => '否','1' => '是'),
                    'companyThree'	=> $userInfo['u_companyThree'],
                    'companyThreeType' => array('0' => '否','1' => '是'),
                    'certNum' => $userInfo['u_certNum'],
                    'comOrgCode' => $userInfo['u_comOrgCode'],
                    'comTaxCode' => $userInfo['u_comTaxCode'],
                    'countryType' => $countryType,
                    'specialAllow' => $userInfo['u_specialAllow'],
                    'indFull' => $indFull,
                    'areaFull' => $areaFull,
                    'areaUnionFull' => $areaUnionFull,
            ];
            $this->show(message::getJsonMsgStruct('1001', $info));//参数错误
            exit;			
        }	
	}
}
