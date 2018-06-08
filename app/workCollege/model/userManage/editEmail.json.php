<?php

class editEmail_json extends worker {
    private $isFlow;
    function __construct($options) {        		
        if (isset($options['flow_id']) && intval($options['flow_id'])) {
            parent::__construct($options, [30601]);
            $this->isFlow = true;    
            $flow = new flow($options['flow_id']);
            if (!($flow->checkPower($options['step'],$_SESSION['userID'],$options['fid']))) {
                $this->show(message::getJsonMsgStruct('0005'));
                exit;
            }
        }else{
            parent::__construct($options, [60117]);			
        }
    }
    function run() {
        //         dump($this->options);die;
        $id = isset($this->options['id'])?$this->options['id']:"";
        $email = isset($this->options['email'])?$this->options['email']:"";
        if ($id == "" || $email == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }

        $userInfo = json_decode(apis::request('/u/api/publicUserInfo.json',array('u_id'=>$id)),true);
        if ($userInfo['code'] != '1001') {
            $this->show(message::getJsonMsgStruct('1002', '用户信息错误'));
        }

        $userInfo  = $userInfo['data'];
        $authed    = str_split($userInfo['u_auth']);
        $authed[1] = 0;
        $authed    = implode('',$authed);

        $db = new MySql();
        $user = new user($db);
        $oldEmail = $db->getField("select u_email from t_user where u_id='".$id."'");
        if(!$user->uniqueUserInfo(8, $email)){
            $this->show(message::getJsonMsgStruct('2004', '邮箱账号已被占用'));//邮箱错误
            exit;
        }

        $data = array(
            'u_email'  => $email,
            'u_auth'  => $authed,
        );

        try{
            $db->beginTRAN();
            $result = $db->update('t_user',$data, "u_id = '".$id."'");
            if ($result){
                //记录操作日志
                $data['memo'] = '修改用户邮箱操作';
                log::writeLogMongo(60117, 't_user', $id, $data);

                //历史操作
                $userTran = array(
                    'ut_uid'	  => $id,
                    'ut_type'	  => 13,
                    'ut_eid'	  => $_SESSION['userID'],
                    'ut_ctime'	  => F::mytime(),
                    'ut_oldValue' => $oldEmail,
                    'ut_newValue' => $email,
                    'ut_reason'   =>'修改会员邮箱',
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
                        $data['memo'] = '修改会员邮箱';
                        $flow         = new flow($data['flow_id'],$db);
                        $res          = $flow->flowHistory($data['fid'],$data['step'],1,$data['eid'],$data['formhtml'],$data['file'],$data['memo']);
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
