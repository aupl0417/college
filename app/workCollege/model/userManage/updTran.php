<?php
/*=============================================================================
#     FileName: updTran.php
#         Desc: 修改推荐人
#       Author: Wuyuanhang
#        Email: QQ:554119220
#     HomePage: 
#      Version: 0.0.1
#   LastChange: 2016-07-15 16:44:59
#      History:
=============================================================================*/

class updTran extends worker {
    private $db;
    private $flow;
    function __construct($options) {
        if (isset($options['flow_id']) && intval($options['flow_id'])) {
            parent::__construct($options, [30601]);
            //权限判断
            $this->flow = new flow($options['flow_id']);
            $power      = $this->flow->checkPower($options['step'],$_SESSION['userID'],$options['fid']);
            if (!$power['power']) {
                die($this->show(message::getJsonMsgStruct('0005')));
            }
        }else{
            parent::__construct($options, [60104]);
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

        if (empty($userID)) {
            die($this->show(message::getJsonMsgStruct('1002', '参数错误')));
        }

        //通过当前会员ID，获取其一级推荐人code
        $fCode = $this->db->getField("SELECT u_fCode FROM t_user WHERE u_id='$userID'");

        //获取当前推荐人的昵称，推广码
        $info = $this->db->getRow("select u_nick,u_code from t_user where u_code = '$fCode'");
        $info['u_id'] = $userID;
        $data = array(
            'fid'     => 0,
            'flow_id' => 0,
            'step'    => 0,
        );

        //判断是否来自工单的操作
        if (isset($options['fid'],$options['flow_id'],$options['step'])) {
            if (intval($options['fid']) && intval($options['flow_id']) && intval($options['flow_id'])) {
                //判断当前工单完成的情况
                if (1 == $this->flow->getFlowStepState($options['fid'],$options['step'])) {
                    die($this->show(message::getJsonMsgStruct('1002', '该工单当前步骤已经受理，无需重复操作！')));//参数错误
                }
                $data['fid']     = $options['fid'];
                $data['flow_id'] = $options['flow_id'];
                $data['step']    = $options['step'];
            }
        }

        $info['msg'] = isset($this->options['msg']) && intval($this->options['msg']) ? 1 : 0;
        $info        = array_merge($info,$data);

        $this->setReplaceData($info);
        $this->setTempAndData();
        $this->show();
    }
}

?>
