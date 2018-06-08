<?php
/*=============================================================================
#     FileName: editNick.php
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:56:30
#      History:
#      Paramer:
=============================================================================*/

class editNick extends worker {
    private $flow;
    private $db;
    function __construct($options) {
        if (isset($options['flow_id']) && intval($options['flow_id'])) {
            parent::__construct($options, [30601]);
            //权限判断
            $this->flow = new flow($options['flow_id']);
            $power = $this->flow->checkPower($options['step'],$_SESSION['userID'],$options['fid']);
            if (!$power['power']) {
                $this->show(message::getJsonMsgStruct('0005'));
                exit;
            }
        }else{
            parent::__construct($options, [60115]);
        }

        $this->db = new MySql();

    }
    function run() {
        //         dump($this->options);die;
        //$id = isset($this->options['id'])?$this->options['id']:"";
        $options = $this->options;
        if(isset($options['id'])){
            $id = $options['id'];
        }elseif(isset($options['fid'])){
            $id = $this->db->getField("SELECT fu_uid FROM t_flow_user WHERE fu_id={$options['fid']}");
        }

        if (empty($id)){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $options = $this->options;
        $nick = $this->db->getField("select u_nick from t_user where u_id='".$id."'");
        $data = array(
            'uid'     => $id,
            'nick'    => $nick,
            'fid'     => 0,
            'flow_id' => 0,
            'step'    => 0,
            'msg'     => isset($options['msg']) && intval($options['msg']) ? 1 : 0,
        );

        //判断是否来自工单的操作
        if (isset($options['fid'],$options['flow_id'],$options['step'])) {
            //判断当前工单完成的情况
            if (1 == $this->flow->getFlowStepState($options['fid'],$options['step'])) {
                $this->show(message::getJsonMsgStruct('1002', '该工单当前步骤已经受理，无需重复操作！'));//参数错误
                exit;
            }
            if (intval($options['fid']) && intval($options['flow_id']) && intval($options['flow_id'])) {
                $data['fid']     = $options['fid'];
                $data['flow_id'] = $options['flow_id'];
                $data['step']    = $options['step'];
            }else{
                $this->show(message::getJsonMsgStruct('1002','参数错误'));
                exit;
            }
        }

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
