<?php
/*=============================================================================
#     FileName: writtenOff.php
#         Desc: 会员注销 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-07-19 11:44:42
#      History:
#      Paramer: 
=============================================================================*/

class writtenOff extends worker {
    private $flow;
    private $db;
    function __construct($options) {        		
        if (isset($options['flow_id']) && intval($options['flow_id'])) {
            parent::__construct($options, [30601]);
            //权限判断
            $this->flow = new flow($options['flow_id']);
            $power = $this->flow->checkPower($options['step'],$_SESSION['userID'],$options['fid']);
            if (!$power['power']) {
                die($this->show(message::getJsonMsgStruct('0005')));
            }
        }else{
            parent::__construct($options, [60115]);			
        }

        $this->db = new MySql();

    }
    function run() {
        $options = $this->options;
        if(isset($options['id'])){
            $userID = $options['id'];
        }elseif(isset($options['fid'])){
            $userID = $this->db->getField("SELECT fu_uid FROM t_flow_user WHERE fu_id={$options['fid']}");
        }

        if (empty($userID)){
            die($this->show(message::getJsonMsgStruct('1002','参数错误')));
        }
        $options = $this->options;
        $nick = $this->db->getField("select u_nick from t_user where u_id='".$userID."'");
        $data = array(
            'uid'     => $userID,
            'nick'    => $nick,
            'fid'     => 0,
            'flow_id' => 0,
            'step'    => 0,
        );

        //判断是否来自工单的操作
        if (isset($options['fid'],$options['flow_id'],$options['step'])) {
            //判断当前工单完成的情况
            if (1 == $this->flow->getFlowStepState($options['fid'],$options['step'])) {
                die($this->show(message::getJsonMsgStruct('1002', '该工单当前步骤已经受理，无需重复操作！')));
            }
            if (intval($options['fid']) && intval($options['flow_id']) && intval($options['flow_id'])) {
                $data['fid']     = $options['fid'];
                $data['flow_id'] = $options['flow_id'];
                $data['step']    = $options['step'];
            }else{
                die($this->show(message::getJsonMsgStruct('1002','参数错误')));
            }
        }

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
