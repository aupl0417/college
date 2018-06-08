<?php
/*=============================================================================
#     FileName: editReal.php
#         Desc: 修改真实姓名
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:30:07
#      History:
#      Paramer:
=============================================================================*/

class editReal extends worker {
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
            parent::__construct($options, [60118]);
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
        if (empty($id)){
            die($this->show(message::getJsonMsgStruct('1002','参数错误')));
        }

        $name = $this->db->getField("select u_name from t_user where u_id='".$id."'");
        $data = array(
            'id'      => $id,
            'fid'     => 0,
            'flow_id' => 0,
            'step'    => 0,
            'oldName' => $name,
            'newName' => '',
        );

        //判断是否来自工单的操作
        if (isset($options['fid'],$options['flow_id'],$options['step'])) {
            if (!intval($options['fid']) || !intval($options['flow_id']) || !intval($options['flow_id'])) {
                die($this->show(message::getJsonMsgStruct('1002','参数错误')));
            }

            if (1 == $this->flow->getFlowStepState($options['fid'],$options['step'])) {
                die($this->show(message::getJsonMsgStruct('1002', '该工单当前步骤已经受理，无需重复操作！')));//参数错误
            }
            $data['fid']     = $options['fid'];
            $data['flow_id'] = $options['flow_id'];
            $data['step']    = $options['step'];

            $mgdb = new mgdb();
            $flowUpdInfo = $mgdb->where(array('flow_logID'=>array('value'=>$options['fid'])))->get('userInfoUpdFlow');

            if ($flowUpdInfo) {
                $data['newName'] = pos($flowUpdInfo)['field_value'];
            }
        }

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
