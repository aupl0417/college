<?php
/*=============================================================================
#     FileName: writtenOff.json.php
#         Desc: 注销会员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-20 16:36:04
#      History:
#      Paramer:
=============================================================================*/
class writtenOff_json extends worker {
    function __construct($options) {
        parent::__construct($options, [60114]);
    }

    function run() {
        $options = $this->options;

        if(!isset($options['userID']) || empty($options['userID'])){
            die($this->show(message::getJsonMsgStruct('1002', '会员ID错误')));
        }

        $userID = $options['userID'];
        $db     = new MySql();
        $gplp   = $db->count('t_company_gplp',"cgl_uid='$userID'");

        if ($gplp > 0){
            die($this->show(message::getJsonMsgStruct('1002', '该用户是代理用户')));
        }

        $user     = new user($db);
        $userInfo = $user->getUserByID($userID,'u_type,u_level,u_code,u_nick');

        if (!$userInfo) {
            die($this->show(message::getJsonMsgStruct('1002', '获取用户信息失败')));
        }

        if(1 == $userInfo['u_type']){
            //企业用户
            $isUnionSeller = $db->getField("select u_isUnionSeller from t_user_company u_id where u_id='".$userID."'");

            if (1 == $isUnionSeller){
                die($this->show(message::getJsonMsgStruct('1002', '该用户是联盟商家,注销失败！')));
            }
        }

        try{
            $db->beginTRAN();
            //工单

            //注销操作
            $whereUserID = " u_id = '".$userID."'";
            if(0 == $userInfo['u_type']){
                $dataPer = array('u_certNum' => '',);
                $res     = $db->update('t_user_person',$dataPer,$whereUserID);
            }else{
                $dataCom = array(
                    'u_comLicenseCode' => '',
                    'u_companyName'    => '',
                    'u_comOrgCode'     => '',
                    'u_comTaxCode'     => '',
                );

                $res = $db->update('t_user_company',$dataCom,$whereUserID);
            }

            if($res === false){
                throw new Exception(-1);   //清空会员身份证失败
            }

            //清空账号
            $paramer = array(
                'userID'     => $userID,
                'userNick'   => $userInfo['u_nick'],
                'employeeID' => $_SESSION['userID'],
                'reason'     => '会员注销'
            );
            $logout = apis::request('/pay/api/truncateAccount.json',$paramer,true);

            if (1001 != $logout['code']) {
                throw new Exception($logout['data']);    //'账户清空失败'
            }

            //规则变动
            //清除账户上的积分，唐宝，余额;2 账号冻结不可登录; 3 清除的手机号，身份证3个月后才能再次注册
            $userInfoUpdate = array(
                'u_logout'    => 1,
                //'u_code'      => $newCode, //推荐码加1
                'u_state'     => 0,
                'u_loginPwd'  => '',
                'u_level'     => 1,
                'u_tel'       => '',
                //'u_card'       => '',
                //'u_email'     => '',
                'u_logoutTime'=> F::mytime(),
            );

            $result = $db->update('t_user',$userInfoUpdate,$whereUserID);

            if($result === false){
                throw new Exception(-2);   //清空会员手机，电话失败
            }

            $accountInfo = $logout['data'];

            //$changeLog = array(
            //    'ut_uid'      => $userID,
            //    'ut_type'     => 10,
            //    'ut_eid'      => $_SESSION['userID'],
            //    'ut_ctime'    => F::mytime(),
            //    'ut_oldValue' => 0,
            //    'ut_newValue' => 1,
            //    'ut_reason'   => '用户注销操作',
            //);
            //$change = $db->insert('t_user_tran', $changeLog);
            //if ($change<1){
            //    throw new Exception(-4);    //写入用户表变动表失败
            //}

            //注销操作
            //工单操作start
            if (!isset($options['fid'],$options['flowID'],$options['step']) || !intval($options['fid']) || !intval($options['flowID']) || !intval($options['step'])) {
                throw new Exception(-5);    //'工单信息有误！'
            }

            $updateFlow = array(
                'flow_payStatus' => 1, //已经支付工单服务费
            );

            if (($db->update('t_flow_user',$updateFlow," fu_id={$options['fid']}")) === false) {
                throw new Exception(-6);    //'工单服务费支付状态修改失败！'
            }

            $options['eid']  = $_SESSION['userID'];
            $options['file'] = '';
            $options['memo'] = '会员注销';
            $flow            = new flow($options['flowID'],$db);
            $res             = $flow->flowHistory($options['fid'],$options['step'],1,$options['eid'],$options['formhtml'],$options['file'],$options['memo']);

            if (!$res) {
                if (-33 == $flow->getError()) {
                    throw new Exception(-7);    //'没有设置下一步骤分配人！'
                }else{
                    throw new Exception(-8);    //'工单操作流程失败！'
                }
            }

            //工单end
            $db->commitTRAN();

            //记录操作日志
            $data['memo'] = '注销会员操作';
            log::writeLogMongo(60114, 't_user', $userID, array_merge($userInfoUpdate, $accountInfo));
            $this->show(message::getJsonMsgStruct('1001', "注销成功！"));//成功
        }catch(Exception $e){
            $db->rollBackTRAN();
            switch($e->getMessage()){
            case -1  : $msg='修改会员信息失败';break;
            case -2  : $msg='清空会员手机，电话失败';break;
            case -3  : $msg='账户清空失败';break;
            case -4  : $msg='写入用户表变动表失败';break;
            case -5  : $msg='工单信息有误';break;
            case -6  : $msg='工单服务费支付状态修改失败';break;
            case -7  : $msg='没有设置下一步骤分配人';break;
            case -8  : $msg='工单操作流程失败！';break;
            case -10 : $msg='工单服务费扣除失败！';break;
            case -33 : $msg='会员账号没有设置支付密码！';break;
            default : $msg = '注销失败'; break;
            }
            //$this->show(message::getJsonMsgStruct('1002',$e->getMessage()));//注销失败
            $this->show(message::getJsonMsgStruct('1002',$msg));//注销失败
        }
    }
}
