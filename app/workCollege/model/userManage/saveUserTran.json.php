<?php
/*=============================================================================
#     FileName: saveUserTran.json.php
#         Desc: 修改推荐人 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#     HomePage: 
#      Version: 0.0.1
#   LastChange: 2016-09-11 14:57:01
#      History:
=============================================================================*/
class saveUserTran_json extends worker {
    private $db;
    private $isFlow;
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
            parent::__construct($options, [60104]);			
        }

        $this->db = new MySql();
    }

    function run() {
        $user    = new user();
        $options = $this->options;

        if(!isset($options['userID']) || empty($options['userID']) ){
            die($this->show(message::getJsonMsgStruct('1002', '参数错误:userID')));//参数错误
        }

        if(!isset($options['nick']) || empty($options['nick']) ){
            die($this->show(message::getJsonMsgStruct('1002', '参数错误:nick')));
        }

        if(!isset($options['reason']) || empty($options['reason']) ){
            die($this->show(message::getJsonMsgStruct('1002', '参数错误:$reason')));//参数错误
        }

        $userID = $options['userID'];
        $nick   = trim($options['nick']);
        $reason = $options['reason'];

        $selectUserInfoSql = "SELECT u_id,u_nick,u_code,u_fCode,u_fCode2,u_fCode3,u_cNum,u_cNum2,u_cNum3,u_createTime FROM t_user WHERE %s"; //查询与修改推荐人相关的会员信息的公用SQL

        //新的推荐人
        $newRecommendInfo = $this->db->getRow(sprintf($selectUserInfoSql,"u_nick='$nick'"));

        if(!$newRecommendInfo){
            die($this->show(message::getJsonMsgStruct('1002', '没有此推荐人')));
        }

        $newfCode = $newRecommendInfo['u_code'];

        if ($newfCode < 1000){
            die($this->show(message::getJsonMsgStruct('1002', '不能修改成admin为推荐人')));
        }

        //要修改推荐人的会员信息
        $userInfo = $this->db->getRow(sprintf($selectUserInfoSql,"u_id='$userID'"));

        //原推荐人信息
        $preRecommendInfo = $this->db->getRow(sprintf($selectUserInfoSql,"u_code='{$userInfo['u_fCode']}'"));

        //新推荐人注册时间要比当前用户晚
        //if($newfCode > $userInfo['u_code']){
        if(strtotime($newRecommendInfo['u_createTime']) > strtotime($userInfo['u_createTime'])){
            die($this->show(message::getJsonMsgStruct('1002', '推荐人注册时间不能晚于被推荐人')));
        }

        //$recommendNum = $this->db->count('t_user',"u_fcode='{$userInfo['u_code']}'");   //不理解是u_cNum > 1000 还是 u_cNum+u_cNum2+u_cNum3>1000
        $recommendNum = $this->db->getField("SELECT u_cNum FROM t_user WHERE u_id='$userID'");
        if($recommendNum > 1000){
            die($this->show(message::getJsonMsgStruct('1002', '推荐人数目超过1千个，不能直接修改')));
        }

        try{
            $this->db->beginTRAN();
            //判断是否带走关系树(备用)
            //$moveTree = true;

            //人走，关系树走start
            //第一步-修改会员资料
            $userInfoUpdate = array(
                'u_fCode'  => $newfCode,
                'u_fCode2' => $newRecommendInfo['u_fCode'],
                'u_fCode3' => $newRecommendInfo['u_fCode2'],
            );
            if(false === $this->db->update('t_user', $userInfoUpdate, " u_id='".$userID."'")){
                throw new Exception(-3);   //会员资料更新失败
            }

            //第二步-修改原推荐人的数据
            $sql = "UPDATE t_user SET u_cNum=u_cNum-1,u_cNum2=u_cNum2-{$userInfo['u_cNum']},u_cNum3=u_cNum3-{$userInfo['u_cNum2']} "
                ." WHERE u_id='{$preRecommendInfo['u_id']}'";
            if(!$this->db->exec($sql)){
                throw new Exception(-4);   //修改原推荐人的数据错误
            }

            //第三步-修改现在推荐人的数据
            $sql = "UPDATE t_user SET u_cNum=u_cNum+1,u_cNum2=u_cNum2+{$userInfo['u_cNum']},u_cNum3=u_cNum3+{$userInfo['u_cNum2']}"
                ." WHERE u_id='{$newRecommendInfo['u_id']}'";

            if(!$this->db->exec($sql)){
                throw new Exception(-5);   //修改现在推荐人的数据错误
            }

            //第四步-修改现在推荐人的推荐人的数据
            $newRecommendTranInfo = $this->db->getRow(sprintf($selectUserInfoSql,"u_code='{$newRecommendInfo['u_fCode']}'"));
            if ($newRecommendTranInfo) {
                $sql = "UPDATE t_user SET u_cNum2=u_cNum2+1,u_cNum3=u_cNum3+{$userInfo['u_cNum']} WHERE u_id='{$newRecommendTranInfo['u_id']}'";
                if(!$this->db->exec($sql)){
                    throw new Exception(-6);   //修改现在推荐人的推荐人的数据错误
                }

                //第五步-修改现在推荐人的推荐人的推荐人的数据
                $newRecommendTranParentInfo = $this->db->getRow(sprintf($selectUserInfoSql,"u_code='{$newRecommendTranInfo['u_fCode']}'"));
                if ($newRecommendTranParentInfo) {
                    $sql = "UPDATE t_user SET u_cNum3=u_cNum3+1 WHERE u_id='{$newRecommendTranParentInfo['u_id']}'";
                    if(!$this->db->exec($sql)){
                        throw new Exception(-7);   //修改现在推荐人的推荐人的推荐人的数据错误
                    }
                }
            }

            //第六步-修改会员的直推会员的数据
            $sql = "UPDATE t_user SET u_fCode2={$newRecommendInfo['u_code']},u_fCode3={$newRecommendInfo['u_fCode']} WHERE u_fcode='{$userInfo['u_code']}'";
            if($this->db->exec($sql) === false){
                throw new Exception(-8);   //修改会员的直推会员的数据
            }

            //第七步-修改会员的二级推会员的数据
            $sql = "UPDATE t_user SET u_fCode3=$newfCode WHERE u_fcode2='{$userInfo['u_code']}'";
            if($this->db->exec($sql) === false){
                throw new Exception(-9);   //修改会员的二级推会员的数据
            }

            //人走，关系树走end
            //修改关系树结束

            //写入用户表变动表
            $changeLog = array(
                'ut_uid'	  => $userID,
                'ut_type'	  => 3,
                'ut_eid'	  => $_SESSION['userID'],
                'ut_ctime'	  => F::mytime(),
                'ut_oldValue' => $preRecommendInfo['u_code'],
                'ut_newValue' => $newfCode,
                'ut_reason'   => $reason,
            );

            //写入用户表变动表
            if(!$this->db->insert('t_user_tran', $changeLog)){
                throw new Exception(-10);   //'写入用户表变动表失败'
            }
            //工单流程开始
            if (!isset($options['fid'],$options['flowID'],$options['step']) || !intval($options['fid']) || !intval($options['flowID']) || !intval($options['step'])) {
                throw new Exception(-14);   //'工单信息有误！'
            }

            $updateFlow = array(
                'flow_payStatus' => 1, //已经支付工单服务费
            );

            if (($this->db->update('t_flow_user',$updateFlow," fu_id={$options['fid']}")) === false) {
                throw new Exception(-11);   //'工单服务费支付状态修改失败！'
            }

            $options['eid']  = $_SESSION['userID'];
            $options['file'] = '';
            $options['memo'] = '修改推荐人';
            $flow            = new flow($options['flowID'],$this->db);
            $res             = $flow->flowHistory($options['fid'],$options['step'],1,$options['eid'],$options['formhtml'],$options['file'],$options['memo']);

            if (!$res) {
                if (-33 == $flow->getError()) {
                    throw new Exception(-12);   //'没有设置下一步骤分配人！'
                }else{
                    throw new Exception(-13);   //'工单操作流程失败！'
                }
            }
            //工单流程结束

            $userInfoUpdate['memo'] = '修改推荐人';
            log::writeLogMongo(60103, 't_user', $userID, $changeLog);
            $this->db->commitTRAN();
            $this->show(message::getJsonMsgStruct('1001','修改成功'));
        }
        catch(Exception $e){			
            $this->db->rollBackTRAN();
            switch ($e->getMessage()) {
            case -1  : $msg = '选择支付方式错误'; break;
            case -2  : $msg = '工单服务费收取失败'; break;
            case -4  : $msg = '会员资料更新失败'; break;
            case -5  : $msg = '获取需要修改推荐人的会员信息失败'; break;
            case -6  : $msg = '修改原推荐人的数据错误'; break;
            case -7  : $msg = '修改现在推荐人的推荐人的推荐人的数据错误'; break;
            case -8  : $msg = '修改会员的直推会员的数据'; break;
            case -9  : $msg = '修改会员的二级推会员的数据'; break;
            case -10 : $msg = '写入用户表变动表失败'; break;
            case -11 : $msg = '工单服务费支付状态修改失败'; break;
            case -12 : $msg = '没有设置下一步骤分配人'; break;
            case -13 : $msg = '工单操作流程失败'; break;
            case -14 : $msg = '工单操作流程失败'; break;
            default  : $msg = '修改推荐人失败';   break;
            }

            //将扣除的费用归还
            //$paramer['type'] = 1;
            //$flowCostRes = apis::request('/pay/api/flowCost.json',$paramer,true);
            //$this->show(message::getJsonMsgStruct('1002', $e->getMessage()));//错误
            $this->show(message::getJsonMsgStruct('1002', $msg));//错误
        }
    }
}
