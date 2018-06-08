<?php
/*=============================================================================
#     FileName: editTel.php
#         Desc: 修改手机号码
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:56:57
#      History:
#      Paramer:
=============================================================================*/

class editTel extends worker {
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
            parent::__construct($options, [60116]);
        }
        $this->db = new MySql();
    }
    function run() {
        $options = $this->options;
        if(isset($options['id'])){
            $id = $options['id'];
        }elseif(isset($options['fid'])){
            $id = $this->db->getField("SELECT fu_uid FROM t_flow_user WHERE fu_id={$options['fid']}");
        }

        if ($id == ""){
            die($this->show(message::getJsonMsgStruct('1002','参数错误')));
        }

        $tel = $this->db->getField("select u_tel from t_user where u_id='".$id."'");
        $data = array(
            'id'      => $id,
            'tel'     => $tel,
            'fid'     => 0,
            'flow_id' => 0,
            'step'    => 0,
            'oldTel'  => $tel,
            'newTel'  => '',
        );

        //判断是否来自工单的操作
        if (isset($options['fid'],$options['flow_id'],$options['step'])) {
            //判断当前工单完成的情况
            if (1 == $this->flow->getFlowStepState($options['fid'],$options['step'])) {
                die($this->show(message::getJsonMsgStruct('1002', '该工单当前步骤已经受理，无需重复操作！')));//参数错误
            }

            if (!intval($options['fid']) || !intval($options['flow_id']) || !intval($options['flow_id'])) {
                die($this->show(message::getJsonMsgStruct('1002','参数错误')));
            }

            $data['fid']     = $options['fid'];
            $data['flow_id'] = $options['flow_id'];
            $data['step']    = $options['step'];

            $flowReason = $this->db->getField("SELECT fu_reason FROM t_flow_user WHERE fu_id={$options['fid']}");
            if (!empty($flowReason)) {
                preg_match_all("/新手机:(\w+)\)/i",$flowReason,$match);

                $data['newTel'] = $match[1][0];
            }
        }

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
