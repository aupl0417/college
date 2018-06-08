<?php
/*=============================================================================
#     FileName: editLicense.php
#         Desc: 修改公司营业执照
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-09-19 17:56:38
#      History:
#      Paramer:
=============================================================================*/

class editLicense extends worker {
    private $isFlow;
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
            parent::__construct($options, [60121]);
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

        $license = $this->db->getRow("select u_comLicenseCode,u_companyType from t_user_company where u_id='".$id."'");
        $organize = F::getAttrs(19);
        $organize = F::array2Options($organize,[$license['u_companyType']]);

        $data = array(
            'uid'        => $id,
            'license'    => $license['u_comLicenseCode'],
            'organize'   => $organize,
            'fid'        => 0,
            'flow_id'    => 0,
            'step'       => 0,
            'newLicense' => '',
        );

        //判断是否来自工单的操作
        if (isset($options['fid'],$options['flow_id'],$options['step'])) {
            if (intval($options['fid']) && intval($options['flow_id']) && intval($options['flow_id'])) {
                //判断当前工单完成的情况
                if (1 == $this->flow->getFlowStepState($options['fid'],$options['step'])) {
                    die($this->show(message::getJsonMsgStruct('1002', '该工单当前步骤已经受理，无需重复操作！')));
                }
                $data['fid']     = $options['fid'];
                $data['flow_id'] = $options['flow_id'];
                $data['step']    = $options['step'];

                $mgdb = new mgdb();
                $flowUpdInfo = $mgdb->where(array('flow_logID'=>array('value'=>$options['fid'])))->get('userInfoUpdFlow');
                if ($flowUpdInfo) {
                    $data['newLicense'] = pos($flowUpdInfo)['field_value'];
                }

            }else{
                die($this->show(message::getJsonMsgStruct('1002','参数错误')));
            }
        }

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
