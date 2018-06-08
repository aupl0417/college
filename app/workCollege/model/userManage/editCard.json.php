<?php
/*=============================================================================
#     FileName: editCard.json.php
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:49:15
#      History:
#      Paramer:
=============================================================================*/

class editCard_json extends worker {
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
            parent::__construct($options, [60119]);
        }
    }
    function run() {
//         dump($this->options);die;
        $id = isset($this->options['id'])?$this->options['id']:"";
        $type = isset($this->options['certType'])?$this->options['certType']:"";
        $card = isset($this->options['card'])?$this->options['card']:"";
        if ($id == "" || $type == "" || $card == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $db = new MySql();
        $user = new user($db);
        $oldCard = $db->getRow("select u_certType,u_certNum from t_user_person where u_id='".$id."'");
        if ($type == 1){
            $alredy = $db->getField("select u_certNum from t_user_person where u_id!='".$id."' and u_certNum='".$card."'");
            if($alredy){
                $this->show(message::getJsonMsgStruct('2006'));//身份证错误
                exit;
            }
        }else{
            if (!idcard::idcard_checksum18($card)) {
                $this->show(message::getJsonMsgStruct('2006',"身份证不合法"));//身份证错误
                exit;
            }
            $alredy = $db->getField("select u_certNum from t_user_person where u_id!='".$id."' and u_certNum='".$card."'");
            if($alredy){
                $this->show(message::getJsonMsgStruct('2006'));//身份证错误
                exit;
            }
        }
        $data = array(
            'u_certType'  => $type,
            'u_certNum'   => $card,
        );
        if ($oldCard['u_certType'] ==1){
            $oldCard['u_certType'] = '非大陆身份证';
        }else{
            $oldCard['u_certType'] = "大陆身份证";
        }
        try{
            $db->beginTRAN();
            $result = $db->update('t_user_person',$data, "u_id = '".$id."'");
            if (1 != $result){
                throw new Exception('-1');
            }

            //记录操作日志
            $data['memo'] = "修改用户证件号码操作:{$oldCard['u_certNum']} --> $card";
            log::writeLogMongo(60119, 't_user_person', $id, $data);

            //历史操作
            //$userTran = array(
            //    'ut_uid'	  => $id,
            //    'ut_type'	  => 15,
            //    'ut_eid'	  => $_SESSION['userID'],
            //    'ut_ctime'	  => F::mytime(),
            //    'ut_oldValue' => $oldCard['u_certType'].",".$oldCard['u_certNum'],
                //    'ut_newValue' => $data['u_certType'].",".$data['u_certNum'],
                //    'ut_reason'   =>'修改会员身份证',
                //);
                //$resNk = $db->insert("t_user_tran", $userTran);
                //if(!$resNk){
                //    throw new Exception('-1');
                //}

                //如果是通过工单来操作 工单流程start
                $data = $this->options;
                if ($this->isFlow) {
                    if (isset($data['fid'],$data['flow_id'],$data['step']) && intval($data['fid']) && intval($data['flow_id']) && intval($data['step'])) {
                        $data['eid']  = $_SESSION['userID'];
                        $data['file'] = '';
                        $data['memo'] = '修改会员身份证';
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
        }catch(Exception $e){
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002','操作失败!'));
        }
    }
}
