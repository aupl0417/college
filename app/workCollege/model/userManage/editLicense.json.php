<?php
/*=============================================================================
#     FileName: editLicense.json.php
#         Desc: 修改公司营业执照
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:55:59
#      History:
#      Paramer:
=============================================================================*/

class editLicense_json extends worker {
    private $isFlow;
    function __construct($options) {
        if (isset($options['flow_id']) && intval($options['flow_id'])) {
            parent::__construct($options, [30601]);
            $this->isFlow = true;
            $flow = new flow($options['flow_id']);
            $power = $flow->checkPower($options['step'],$_SESSION['userID'],$options['fid']);
            if (!$power['power']) {
                $this->show(message::getJsonMsgStruct('0005'));
                exit;
            }
        }else{
            parent::__construct($options, [60121]);
        }
    }

    function run() {
        $options = $this->options;
        $id       = isset($options['id'])       ? $options['id']       : '';
        $license  = isset($options['license'])  ? $options['license']  : '';
        $organize = isset($options['organize']) ? $options['organize'] : '';

        if ($id == '' || $license=='' || $organize==''){
            die($this->show(message::getJsonMsgStruct('1002','参数错误')));
        }

        $userInfo = apis::request('/u/api/publicUserInfo.json',array('u_id'=>$id),true);
        if ($userInfo['code'] != '1001') {
            $this->show(message::getJsonMsgStruct('1002', '用户信息错误'));
        }

        $userInfo  = $userInfo['data'];
        $authed    = str_split($userInfo['u_auth']);
        $authed[2] = 0;
        $authed    = implode('',$authed);

        $db   = new MySql();
        $user = new user($db);

        $oldLicense = array(
            'u_comLicenseCode' => $userInfo['u_comLicenseCode'],
            'u_companyType'    => $userInfo['u_companyType'],
        );

        $result = $db->getRow("select u_id from t_user_company where u_id!='".$id."' and u_comLicenseCode='".$license."'");

        if($result){
            die($this->show(message::getJsonMsgStruct('2008')));//营业执照错误
        }

        $data = array(
            'u_comLicenseCode' => $license,
            'u_companyType'    => $organize,
        );

        $org     = F::getAttrs(19);
        $oldType = '';

        if ($oldLicense['u_companyType']){
            $oldType = $org[$oldLicense['u_companyType']];
        }

        $newType = $org[$organize];

        try{
            $db->beginTRAN();
            $result = $db->update('t_user_company',$data, "u_id='".$id."'");
            if (1 != $result){
                throw new Exception('-1');
            }

            //$updAuthRes = $db->update('t_user',array('u_auth'=>$authed), "u_id = '".$id."'");

            //if (1 != $updAuthRes){
            //    throw new Exception('-2');
            //}

            //记录操作日志
            $options['memo'] = "修改用户营业执照编号和组织机构类型操作:{$oldLicense['u_comLicenseCode']} --> {$license}";
            log::writeLogMongo(60121, 't_user', $id, $options);

            //如果是通过工单来操作 工单流程start
            if ($this->isFlow) {
                if (isset($options['fid'],$options['flow_id'],$options['step']) && intval($options['fid']) && intval($options['flow_id']) && intval($options['step'])) {
                    $options['eid']  = $_SESSION['userID'];
                    $options['file'] = '';
                    $options['memo'] = '修改用户营业执照编号';
                    $flow = new flow($options['flow_id'],$db);
                    $res = $flow->flowHistory($options['fid'],$options['step'],1,$options['eid'],$options['formhtml'],$options['file'],$options['memo']);
                    if (!$res) {
                        throw new Exception(-11);
                    }
                }else{
                    throw new Exception(-12);
                }
            }

            $db->commitTRAN();
            $this->show(message::getJsonMsgStruct('1001', "修改成功！"));//成功
        }catch(Exception $e){
            $db->rollBackTRAN();
            switch($e->getMessage()){
            case -1: $msg='修改营业执照失败';break;
            case -2: $msg='修改用户认证状态失败,如果未通过企业认证，无需走工单修改营业执照';break;
            case -11: $msg='工单流程错误';break;
            case -12: $msg='工单流程错误';break;
            default:  $msg='修改失败';break;
            }
            $this->show(message::getJsonMsgStruct('1002',$msg));
        }
    }
}
