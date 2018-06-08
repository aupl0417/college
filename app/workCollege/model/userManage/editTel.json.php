<?php
/*=============================================================================
#     FileName: editTel.json.php
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:57:01
#      History:
#      Paramer:
=============================================================================*/

class editTel_json extends worker {
    private $isFlow;
    function __construct($options) {
        if (isset($options['flow_id']) && intval($options['flow_id'])) {
            parent::__construct($options, [30601]);
            $this->isFlow = true;
            $flow = new flow($options['flow_id']);
            $power = $flow->checkPower($options['step'],$_SESSION['userID'],$options['fid']);
            if (!$power['power']) {
                die($this->show(message::getJsonMsgStruct('0005')));
            }
        }else{
            parent::__construct($options, [60116]);
        }
    }

    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        $tel = isset($this->options['tel'])?$this->options['tel']:"";
        if ($id == ""){
            die($this->show(message::getJsonMsgStruct('1002','参数错误')));
        }

        $userInfo = apis::request('/u/api/publicUserInfo.json',array('u_id'=>$id),true);
        if ($userInfo['code'] != '1001') {
            $this->show(message::getJsonMsgStruct('1002', '用户信息错误'));
        }

        $userInfo  = $userInfo['data'];
        $authed    = str_split($userInfo['u_auth']);
        $authed[0] = 0;
        $authed    = implode('',$authed);

        $db = new MySql();
        $user = new user($db);
        $oldTel = $db->getField("select u_tel from t_user where u_id='".$id."'");
        if ($tel){
            if(!$user->uniqueUserInfo(2, $tel)){
                die($this->show(message::getJsonMsgStruct('2004')));//手机错误
            }
        }
        $data = array(
            'u_tel'  => $tel,
            'u_auth'  => $authed,
        );

        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改用户手机号码操作';
                //log::writeLogMongo(60116, 't_user', $id, $data);

                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 12,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldTel,
                    'ut_newValue' => $tel,
                    'ut_reason'   =>'修改用户手机号码操作',
                );
                $resNk = $db->insert("t_user_tran", $userTran);
                if(!$resNk){
                    throw new Exception('-1');
                }

                //如果是通过工单来操作 工单流程start
                $data = $this->options;
                if ($this->isFlow) {
                    if (isset($data['fid'],$data['flow_id'],$data['step']) && intval($data['fid']) && intval($data['flow_id']) && intval($data['step'])) {
                        $data['eid']  = $_SESSION['userID'];
                        $data['file'] = '';
                        $data['memo'] = '修改手机号码';
                        $flow = new flow($data['flow_id'],$db);
                        $res = $flow->flowHistory($data['fid'],$data['step'],1,$data['eid'],$data['formhtml'],$data['file'],$data['memo']);
                        if (!$res) {
                            throw new Exception($flow->getError());
                        }
                    }else{
                        throw new Exception(-12);
                    }
                }
                $db->commitTRAN();
                $this->show(message::getJsonMsgStruct('1001','修改成功'));
            }else{
                throw new Exception('-1');
            }
        }catch(Exception $e){
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002','操作失败!'));
        }
    }
}
