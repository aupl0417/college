<?php
/*=============================================================================
#     FileName: quit.json.php
#         Desc: 会员退会
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-23 15:48:10
#      History:
#      Paramer:
=============================================================================*/

class quit_json extends worker {
	function __construct($options) {
        if (isset($options['flowID']) && intval($options['flowID'])) {
            parent::__construct($options, [30601]);
            $this->isFlow = true;
            $flow         = new flow($options['flowID']);
            $power        = $flow->checkPower($options['step'],$_SESSION['userID'],$options['fid']);

            if (!$power['power']) {
                die($this->show(message::getJsonMsgStruct('0005')));
            }
        }else{
            parent::__construct($options, [60112]);
        }

        $this->db = new MySql();
    }

    function run() {
		$options = $this->options;

        if(!isset($options['userID']) || empty($options['userID'])){
            die($this->show(message::getJsonMsgStruct('1002', '会员ID错误')));
        }

        $userID = $options['userID'];

		if ($this->db->count('t_company_gplp',"cgl_uid='$userID'")){
		    die($this->show(message::getJsonMsgStruct('1002', '该用户是代理用户')));
		}

        $user     = new user($this->db);
        $userInfo = $user->getFullUserInfo($userID);

        if(1 == $userInfo['u_type'] && 1 == $userInfo['u_isUnionSeller']){
            die($this->show(message::getJsonMsgStruct('1002', '该用户是联盟商家,注销失败！')));
		}

		$data = array(
		    'u_isQuit'   => 1,
		    'u_state'    => 0,
		    'u_level'    => 1,
		    'u_loginPwd' => '',
		    'u_quitTime' => F::mytime(),
		);

		try{
		    $this->db->beginTRAN();
            $mgdb = new mgdb();
            $where = array(
                'flow_id'    => array('value'=>$options['flowID']),
                'flow_logID' => array('value'=>$options['fid']),
                'user_id'    => array('value'=>$userID),
            );

            $flowUpdInfo = $mgdb->where($where)->get('userInfoUpdFlow');

            if (!$flowUpdInfo) {
               throw new Exception('-1');
            }

            $flowUpdInfo = pos($flowUpdInfo);

            //清空账号
            $paramer = array(
                'userID'     => $userID,
                'userNick'   => $userInfo['u_nick'],
                'employeeID' => $_SESSION['userID'],
                'reason'     => '会员退会'
            );

            $truncateAccountRes = apis::request('/pay/api/truncateAccount.json',$paramer,true);

            if (1001 != $truncateAccountRes['code']) {
                throw new Exception(-3);
            }

            $result = $this->db->update('t_user',$data, "u_id = '$userID'");

            if (1 != $result) {
                throw new Exception('-2');
            }

            $changeLog = array(
                '退会时间' => F::mytime(),
                '旧值'     => 0,
                '新值'     => 1,
                '银行'     => $flowUpdInfo['bankName'],
                '开户行'   => $flowUpdInfo['bankAddress'],
                '开户地区' => $flowUpdInfo['bankCity'],
                '银行卡号' => $flowUpdInfo['cardNum'],
                '备注'     => '用户退会操作'
            );

            //工单操作start
            if (!isset($options['fid'],$options['flowID'],$options['step']) || !intval($options['fid']) || !intval($options['flowID']) || !intval($options['step'])) {
                throw new Exception(-5);    //'工单信息有误！'
            }

            $updateFlow = array(
                'flow_payStatus' => 1, //已经支付工单服务费
            );

            if (($this->db->update('t_flow_user',$updateFlow," fu_id={$options['fid']}")) === false) {
                throw new Exception(-6);    //'工单服务费支付状态修改失败！'
            }

            $options['eid']  = $_SESSION['userID'];
            $options['file'] = '';
            $options['memo'] = '会员退会';
            $flow         = new flow($options['flowID'],$this->db);
            $res          = $flow->flowHistory($options['fid'],$options['step'],1,$options['eid'],$options['formhtml'],$options['file'],$options['memo']);

            if (!$res) {
                if (-33 == $flow->getError()) {
                    throw new Exception(-7);    //'没有设置下一步骤分配人！'
                }else{
                    throw new Exception(-8);    //'工单操作流程失败！'
                }
            }
            //工单end

            log::writeLogMongo(60112, 't_user', $userID, $changeLog);
            $this->db->commitTRAN();
            $this->show(message::getJsonMsgStruct('1001', "退会成功！"));
        }catch(Exception $e){
            $this->db->rollBackTRAN();
            switch($e->getMessage()){
            case -1: $msg = '获取退款信息失败';break;
            case -2: $msg = '修改用户信息失败';break;
            case -3  : $msg='账户清空失败';break;
            case -7  : $msg='没有设置下一步骤分配人';break;
            case -8  : $msg='工单操作流程失败！';break;
            default: $msg = '退会失败'; break;
            }
            $this->show(message::getJsonMsgStruct('2099',$msg));
        }
    }
}
