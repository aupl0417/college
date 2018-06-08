<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/24
 * Time: 11:48
 */
 class userTypeChange_json extends worker{
    function __construct($options) {
        parent::__construct($options, [609]);
    }

    function run() {
        $this->options['id'] = isset($this->options['id']) ? $this->options['id'] : '' ;
        $this->options['type'] = isset($this->options['type']) ? $this->options['type'] : 0;
        $this->options['comLicenseCode'] = isset($this->options['comLicenseCode']) ? $this->options['comLicenseCode'] : '';
        $this->options['certNum'] = isset($this->options['certNum']) ? $this->options['certNum'] : '';

        $db = new MySql();
        $sql = "select u_type from t_user where u_id = '".$this->options['id']."'";
        $result = $db->getRow($sql);

        if($result && ($result['u_type'] == $this->options['type'])){
            return $this->show(message::getJsonMsgStruct('1002','与当前身份一致,无需修改'));
        }else{
            $user = new user();
            $userInfo = $user->getUserByID($this->options['id']);
            try{
                $db->beginTRAN();
                if($userInfo['u_type'] == '1'){//企业用户
                    if($userInfo['u_isUnionSeller'] == 1){
                        $tab = 't_union_companyex';
                        $where = " uc_uid = '".$this->options['id']."'";
                        $resDel = $db->delete($tab,$where);
                        if(!$resDel){
                            throw new Exception('-1');
                        }
                    }

                    if(!$user->uniqueUserInfo(4,$this->options['certNum'],$userInfo['u_id'])){
                        return $this->show(message::getJsonMsgStruct('1002','身份证已被占用'));
                    }

                    $infoUser = array(
                        'u_auth' => substr($userInfo['u_auth'],0,2).'000000000000',
                        'u_type' => 0,
                        'u_isUnionSeller' => 0,
                        'u_companyName' => '',
                        'u_comLicenseCode' => '',
                        'u_comOrgCode' => '',
                        'u_comTaxCode' => '',
                        'u_certNum' => $this->options['certNum'],
                    );

                }else{  //个人用户
                    if(!$user->uniqueUserInfo(7,$this->options['comLicenseCode'],$userInfo['u_id'])){
                        return $this->show(message::getJsonMsgStruct('1002','营业执照已被占用或格式不对'));
                    }

                    $infoUser = array(
                        'u_auth' => substr($userInfo['u_auth'],0,2).'000000000000',
                        'u_type' => 1,
                        'u_comLicenseCode' => $this->options['comLicenseCode'],
                    );

                }

                $tab = 't_user';
                $where = " u_id = '".$this->options['id']."'";
                $res = $db->update($tab,$infoUser,$where);
                if(!$res){
                    throw new Exception('-1');
                }
                //记录操作日志
                $infoUser['u_nick'] = $userInfo['u_nick'];
                $infoUser['memo'] = '会员转换身份';//尽量写得详细一点点了
                log::writeLogMongo(609, 't_user', $this->options['id'], array_merge($infoUser,$userInfo));

                $sql = "select au_id,au_uid from t_auth where au_uid = '".$this->options['id']."' and au_type = 2 and (au_result = 1 or au_result = 0) order by au_ctime desc limit 1";
                $resAuth = $db->getRow($sql);
                if($resAuth){
                    $tab = 't_auth';
                    $info = array(
                        'au_result' => -1,
                        'au_reply'  => '因您会员身份转换，需重新进行身份认证!',
                    );
                    $where = " au_id = '".$resAuth['au_id']."'";
                    $resAu = $db->update($tab,$info,$where);
                    if(!$resAu){
                        throw new Exception('-1');
                    }
                }

                if($userInfo['u_type'] == 0){
                    $userInfo['u_type'] = '个人';
                }else{
                    $userInfo['u_type'] = '企业';
                }
                if($infoUser['u_type'] == 1){
                    $infoUser['u_type'] = '企业';
                }elseif($infoUser['u_type'] == 0){
                    $infoUser['u_type'] = '个人';
                }else{
                    $infoUser['u_type'] = ' - ';
                }

                $userTran = array(
                    'ut_uid'	  => $this->options['id'],
                    'ut_type'	  => 7,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $userInfo['u_type'],
                    'ut_newValue' => $infoUser['u_type'],
                    'ut_reason'   => '会员身份转换',
                );

                $typeChange = $db->insert("t_user_tran", $userTran);
                if(!$typeChange){
                    throw new Exception('-1');
                }

                $db->commitTRAN();
                return $this->show(message::getJsonMsgStruct('1001'));
            }catch(Exception $e){
                $db->rollBackTRAN();
                return $this->show(message::getJsonMsgStruct('1002','操作失败'));
            }
        }
    }

 }