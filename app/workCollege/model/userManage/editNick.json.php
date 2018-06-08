<?php
/*=============================================================================
#     FileName: editNick.json.php
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:56:07
#      History:
#      Paramer:
=============================================================================*/

class editNick_json extends worker {
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
            parent::__construct($options, [60115]);
        }
    }
    function run() {
        $id = isset($this->options['id'])?$this->options['id']:"";
        $nick = isset($this->options['nick'])?$this->options['nick']:"";
        if ($id == "" || $nick==""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = new user($db);
        $oldNick = $db->getField("select u_nick from t_user where u_id='".$id."'");
        if(!$user->uniqueUserInfo(1, $nick)){
            $this->show(message::getJsonMsgStruct('2002'));//用户名格式错误
            exit;
        }
        $data = array(
            'u_nick'  => $nick,
        );

        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");

            //修改账户名称接口
            $res = apis::request('/pay/api/updateAccountNick.json',['u_id' => $id, 'u_nick' => $nick],true);
            if($res['code'] != '1001'){
                throw new Exception('-1');
            }

            if ($result){
                //记录操作日志
                $data['memo'] = '修改用户ID操作';
                log::writeLogMongo(60115, 't_user', $id, $data);

                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 6,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldNick,
                    'ut_newValue' => $nick,
                    'ut_reason'   =>'修改会员ID',
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
                        $data['memo'] = '修改会员ID';
                        $flow = new flow($data['flow_id'],$db);
                        $res = $flow->flowHistory($data['fid'],$data['step'],1,$data['eid'],$data['formhtml'],$data['file'],$data['memo']);
                        if (!$res) {
                            throw new Exception(-11);
                        }
                    }else{
                        throw new Exception(-12);
                    }
                }

                $db->commitTRAN();
                $this->show(message::getJsonMsgStruct('1001', "修改成功！"));//成功
            }else{
                throw new Exception('-1');
            }
        }catch(Exception $e){
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002','操作失败!'));
        }
    }
}
