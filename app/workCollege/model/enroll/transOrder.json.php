<?php

class transOrder_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50010503]);
    }

    function run() {
        $options = $this->options;
        (!isset($options['oriUser'])   || empty($options['oriUser'])) && die($this->show(message::getJsonMsgStruct('1002', '<span class="font-red">请填写报名人账号</span>')));
        (!isset($options['oriMobile']) || empty($options['oriMobile'])) && die($this->show(message::getJsonMsgStruct('1002', '<span class="font-red">请填写报名人手机号</span>')));
//         (!isset($options['purUser'])   || empty($options['purUser'])) && die($this->show(message::getJsonMsgStruct('1002', '<span class="font-red">请填写被转让人用户名</span>')));
        (!isset($options['purMobile']) || empty($options['purMobile'])) && die($this->show(message::getJsonMsgStruct('1002', '<span class="font-red">请填写报到人手机号</span>')));
        (!isset($options['classId'])   || empty($options['classId'])) && die($this->show(message::getJsonMsgStruct('1002', '<span class="font-red">请选择班级</span>')));
        
        $db = new MySql();
        
        $oriUserInfo = $db->getRow('select id,trueName,userId,certNum,level,auth from tang_ucenter_member where username="' . $options['oriUser'] . '" and mobile="' . $options['oriMobile'] . '" and identityType=0');
        !$oriUserInfo && die($this->show(message::getJsonMsgStruct('1002', '报名人不存在，请核实信息后再试')));
        intval($oriUserInfo['level']) < 3    && die($this->show(message::getJsonMsgStruct('1002', '报名人不是【创客】以上的会员')));
        intval($oriUserInfo['auth'][2]) == 0 && die($this->show(message::getJsonMsgStruct('1002', '报名人没有通过【身份】认证')));
        
        //检查该班级是否存在
        $nowDate = date('Y-m-d');
        $classInfo = $db->getRow('select cl_name,cl_description,cl_cost from tang_class where cl_id="' . $options['classId'] . '" and cl_state in (0,1) and cl_status=1');//班级信息
        !$classInfo && die($this->show(message::getJsonMsgStruct('1002', '该班级不在允许报到状态中')));
        
        //检查转让人是否报了该班
        $enrollInfo = $db->getRow('select tse_id,tse_fee,tse_payFee,tse_orderId,tse_state from tang_student_enroll where tse_classId="' . $options['classId'] . '" and tse_userId="' . $oriUserInfo['id'] . '" and tse_status=1 and tse_state<>-1');
        !$enrollInfo && die($this->show(message::getJsonMsgStruct('1002', '报名人已经【报到成功】或者【未报名】该班级，请核实信息后再重试')));
        
        $purUserInfo = $db->getRow('select id,username,trueName,userId,certNum,level,auth from tang_ucenter_member where mobile="' . $options['purMobile'] . '"');
        !$purUserInfo && die($this->show(message::getJsonMsgStruct('1002', '报到人不存在,可先让报到人浏览班级信息后再试')));
        intval($purUserInfo['level']) < 3    && die($this->show(message::getJsonMsgStruct('1002', '报到人不是【创客】以上的会员，请现场升级或走线下换人流程')));
        intval($purUserInfo['auth'][2]) == 0 && die($this->show(message::getJsonMsgStruct('1002', '报到人没有通过【身份】认证，请现场认证或走线下换人流程')));
        
        //检查转让人是否报了该班
        $purCount = $db->getField('select count(tse_id) from tang_student_enroll where tse_classId="' . $options['classId'] . '" and tse_userId="' . $purUserInfo['id'] . '" and tse_state<>-1 limit 1');
        $purCount && die($this->show(message::getJsonMsgStruct('1002', '报到人【已报名】该班级,可直接扫码报到')));
        
        try {
            $db->beginTRAN();
            
            $enrollId = F::getTimeMarkID();
            $time     = date('Y-m-d H:i:s');
            
            //生成orderId
            $params = array(
                'outTradeNo'    => $enrollId,
                'outCreateTime' => $time,
                'buyID'         => $purUserInfo['userId'],
                'buyNick'       => $purUserInfo['username'],
                'totalMoney'    => $enrollInfo['tse_fee'],
                'totalScore'    => 0,
                'subject'       => '班级报名',
                'body'          => '用户' . $options['purUser'] . '报了班级“' . $classInfo['cl_name'] . '”',
                'showUrl'       => 'https://www.dttx.com',//暂时填写
                'dealType'      => 2,
                'returnType'    => 2
            );
            
            $sdk = new openSdk();
            $path = '/order/tcRegister';
            $result = $sdk->request($params, $path);

            if(!is_array($result)){
                throw new Exception($result, -3);
            }
            
            if($result['id'] != 'SUCCESS' && $result['id'] != 'SUCCESS_EMPTY'){
                throw new Exception($result['msg'], -4);
            }
            
            $orderId = $result['info']['order_id'];//获取订单号
            
            if(!$orderId){
                throw new Exception('获取订单号失败', -5);
            }
            
            //插入数据
            $data = array(
                'tse_id'           => $enrollId,
                'tse_pid'          => $enrollInfo['tse_id'],//转让订单id
                'tse_userId'       => $purUserInfo['id'],   //用户id
                'tse_orderId'      => $orderId,
                'tse_userTrueName' => $purUserInfo['trueName'],
                'tse_certNum'      => $purUserInfo['certNum'],
                'tse_classId'      => $options['classId'],
                'tse_fee'          => $classInfo['cl_cost'],
                'tse_payFee'       => 0.00,
                'tse_status'       => 1,
                'tse_state'        => $enrollInfo['tse_state'],
                'tse_createTime'   => $time,
                'tse_payTime'      => $time
            );
            
            $res = $db->insert('tang_student_enroll', $data);
            
            if(!$res){
                throw new Exception('插入报名表失败', -1);
            }
            
            //原订单状态变化为3
            $res = $db->update('tang_student_enroll', array('tse_status' => 3), 'tse_id="' . $enrollInfo['tse_id'] . '"');
            if($res === false){
                throw new Exception('更新订单表失败', -5);
            }
            
            $db->commitTRAN();
            die($this->show(message::getJsonMsgStruct('1001', '操作成功')));
        } catch (Exception $e) {
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct('1002', $e->getMessage())));
        }
        
    }
}
