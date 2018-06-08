<?php
/**
 * Created by PhpStorm.
 * User: JoJoJing
 * Date: 2016/4/27
 * Time: 10:03
 */
class auth extends worker{
    function __construct($options) {
        $powers = array( 0 => '60201', 1 => '60202' );
        $db = new MySql();
        $sql = "select u_type from t_user where u_id = '".$this->options['id']."'";
        $rs = $db->getField($sql);
        parent::__construct($options);
    }

    function run(){
        $id = isset($this->options['id']) ? $this->options['id'] : "";
        if ($id == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $auth = isset($this->options['a']) ? $this->options['a'] : "";
        if($auth == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        $db = new MySql();

        $sql = "select u_type from t_user where u_id = '".$this->options['id']."'";
        $type = $db->getField($sql);
        if(!isset($type)){
            $this->show(message::getJsonMsgStruct('1002','会员身份错误'));
            exit;
        }

        if($type == 0){
            $sql = "select u.*,uc.* from t_user as u left join t_user_person as uc on u.u_id = uc.u_id where u.u_id='".$id."'";
        }else{
            $sql = "select u.*,up.* from t_user as u left join t_user_company as up on u.u_id = up.u_id where u.u_id='".$id."'";
        }
        $info = $db->getRow($sql);

        $certType = array(
            '0' => '中国大陆身份证',
            '1' => '其他证件',
        );
        $rs = apis::request('/u/api/publicUserAuth.json',['u_id' => '43cd406af684047601f36a6554aa8013'],true);
        if($rs['code'] != '1001'){
            $this->show(message::getJsonMsgStruct('1002','无此用户信息，出错'));
            exit;
        }
        $authInfo = $rs['data'];

        $comType = F::getAttrs(4);//企业类型

        if($type == 1){
            $data = array(
                'id'        => $id,
                'type'      => $info['u_type'],
                'auth'      => $auth,
                'nick'      => $info['u_nick'],
                'name'      => $info['u_name'],
                'companyName' => $info['u_companyName'],
                'companyThree' => $info['u_companyThree'],
                'isBranch'  => $info['u_isBranch'],
                'comLegalName' => $info['u_comLegalName'],
                'comLicenseCode' => $info['u_comLicenseCode'],
                'comOrgCode' => $info['u_comOrgCode'],
                'comTaxCode' => $info['u_comTaxCode'],
            );
        }else{
            $data = array(
                'id'        => $id,
                'type'      => $info['u_type'],
                'auth'      => $auth,
                'nick'      => $info['u_nick'],
                'name'      => $info['u_name'],
                'certType'  => $certType[$info['u_certType']],
                'certNum'   => $info['u_certNum'],
            );
        }

        if($authInfo[$auth]['authed'] == 1){
            if($auth == 'mobile'){
                $data['mobile'] = $info['u_tel'];
                $data['authList'] = $authInfo['mobile'];
                $data['authList']['mobile'] = $info['u_tel'];
            }elseif($auth == 'email'){
                $data['email'] = $info['u_email'];
                $data['authList'] = $authInfo['email'];
                $data['authList']['email'] = $info['u_email'];
            }elseif($auth == 'person'){
                $data['authList'] = $authInfo['person'];
                $data['img1']    = $authInfo['person']['detail']['au_imgs_1'];
                $data['img2']    = $authInfo['person']['detail']['au_imgs_2'];
                $data['img3']    = $authInfo['person']['detail']['au_imgs_3'];
            }elseif($auth == 'company'){
                $data['authList'] = $authInfo['company'];
                $data['companyType'] = $comType[$info['u_companyType']];
                $data['img1']    = $authInfo['company']['detail']['au_imgs_1'];
                $data['img2']    = $authInfo['company']['detail']['au_imgs_2'];
                $data['img3']    = $authInfo['company']['detail']['au_imgs_3'];
                $data['img4']    = $authInfo['company']['detail']['au_imgs_4'];
                $data['img5']    = $authInfo['company']['detail']['au_imgs_5'];
                $data['img6']    = $authInfo['company']['detail']['au_imgs_6'];
            }else{
            }
        }

        $infoData = array(
            'tempId'		=> 'temp_'.F::getGID(),
            'jsData' 		=> json_encode($data),
        );

        $this->setReplaceData($infoData);
        $this->setTempAndData();
        $this->show();
    }
}