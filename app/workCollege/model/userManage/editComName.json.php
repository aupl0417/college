<?php
/*=============================================================================
#     FileName: editComName.json.php
#         Desc: 修改公司名称工单发起
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:30:24
#      History:
#      Paramer:
=============================================================================*/

class editComName_json extends worker {
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
            parent::__construct($options, [60126]);
        }
    }

    function run() {
        $id   = isset($this->options['id'])?$this->options['id']:"";
        $name = isset($this->options['name'])?$this->options['name']:"";

        if ($id == "" || $name == ""){
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

        $oldName = $userInfo['u_companyName'];

        if(!$user->uniqueUserInfo(6, $name)){
            die($this->show(message::getJsonMsgStruct('2007')));//营业执照错误
        }

        $data = array(
            'u_companyName'  => $name,
        );

        try{
            $db->beginTRAN();
            $result = $db->update('t_user_company',$data, "u_id = '".$id."'");

            if (1 != $result){
                throw new Exception('-1');
            }

            $updAuthRes = $db->update('t_user',array('u_auth'=>$authed), "u_id = '".$id."'");

            if (false === $updAuthRes){
                throw new Exception('-2');
            }

            //记录操作日志
            $data['memo'] = "修改公司名称操作,$oldName --> $name";
            log::writeLogMongo(60126, 't_user', $id, $data);

            //如果是通过工单来操作 工单流程start
            $data = $this->options;
            if ($this->isFlow) {
                if (isset($data['fid'],$data['flow_id'],$data['step']) && intval($data['fid']) && intval($data['flow_id']) && intval($data['step'])) {
                    $data['eid']  = $_SESSION['userID'];
                    $data['file'] = '';
                    $data['memo'] = '修改企业名称';
                    $flow         = new flow($data['flow_id'],$db);
                    $res          = $flow->flowHistory($data['fid'],$data['step'],1,$data['eid'],$data['formhtml'],$data['file'],$data['memo']);
                    if (!$res) {
                        throw new Exception($flow->getError());
                    }
                }else{
                    throw new Exception(-12);
                }
            }

            $db->commitTRAN();
            $this->show(message::getJsonMsgStruct('1001', "修改成功！"));
        }catch(Exception $e){
            $db->rollBackTRAN();
            //$this->show(message::getJsonMsgStruct('1002',$e->getMessage()));
            $this->show(message::getJsonMsgStruct('1002','操作失败!'));
        }
    }
}
