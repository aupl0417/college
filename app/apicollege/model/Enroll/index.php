<?php
/* 报名接口
 * @param $uname   type : string 用户id   must
 * @param $payType type : int    支付方式      默认 ：0
 * @param $classId type : int    班级id   must
 * @author aupl
 * */
 
 /**
 * @api                    {post} /Enroll/index 报名接口 --已废弃
 * @apiDescription         学员报名
 * @apiName                index
 * @apiGroup               Enroll
 * @apiPermission          aupl 
 *
 * @apiParam {int}        appId        设备类型
   @apiParam {string}     deviceID     设备id
   @apiParam {string}     signValue    签名串
 * @apiParam {int}        classId      课时id
   @apiParam {string}     userId       用户id
   @apiParam {int}        payType      支付方式（可选，默认为0）
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
		code: 1001,
		msg: "报名成功", 
		data: null
	}
 *
 */
class index extends baseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowDate = date('Y-m-d');
    }
	
    function run() {
        //验证参数是否存在
        (!isset($this->data['userId'])  || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(502);
        
        $payType = (isset($this->data['payType']) && !empty($this->data['payType'])) ? $this->data['payType'] + 0 : 0;
        $userId  = $this->data['userId'];
        $classId = $this->data['classId'] + 0;
        
        $params = array();
        
        $user = $this->getUser($this->data['userId']);
        !$user && $this->apiReturn(201);
        
        $isUpdate = false;//判定用户是否升级或者通过身份认证
        if($user['level'] != $this->userInfo['level'] || $user['auth'][2] != $this->userInfo['auth'][2]){
            $isUpdate = true;
        }
        
        ($user['level'] + 0) < 3 && $this->apiReturn(516);
        ($user['auth'][2] + 0) == 0 && $this->apiReturn(517);
        
        //检查该学员有无往期学习记录，如有，则不允许报名
        $records = $this->db->getRow('select count(cs_id) as count,cl_name from tang_class_student LEFT JOIN tang_class on cs_classId=cl_id where cs_studentId="' . $this->uid . '" and cs_classId<>"' . $classId . '"');
        if($records['count']) return apis::apiCallback('1002','您已报过往期班级：' . $records['cl_name']);
        
        //检查该学员是否有过通过审核，但却未报到成功的记录，如有，则不允许报名
        $enrollClass = $this->db->getRow('select count(tse_id) as tse_count,cl_name,(select count(cs_id) from tang_class_student where cs_classId=tse_classId and cs_studentId=tse_userId) as cs_count from tang_student_enroll left join tang_class on cl_id=tse_classId where tse_userId="' . $this->uid . '" and tse_classId<>"' . $classId . '" and tse_state=1 and tse_status=1');
        if($enrollClass['tse_count'] && $enrollClass['cs_count'] == 0){
            return apis::apiCallback(1002, '您已报过往期班级：' . $enrollClass['cl_name'] . '，但未报到！');
        }
        
        //检查该班级是否存在
        $classInfo = $this->db->getRow('select cl_name,cl_description,cl_cost,tangCollege,cl_state as state,cl_enrollStartTime as enrollStartTime,cl_enrollEndTime as enrollEndTime,cl_startTime as startTime from tang_class where cl_id="' . $classId . '" and cl_status=1');//班级信息
        !$classInfo && $this->apiReturn(501); 
        
        $classInfo['state'] == -1 && $this->apiReturn(1002, '该班级已取消');
        $classInfo['state'] == 1  && $this->apiReturn(1002, '该班级已开课');
        $classInfo['state'] == 2  && $this->apiReturn(1002, '该班级已结束');
//         $classInfo['enrollStartTime'] > $this->nowDate && $this->apiReturn(1002, '未到报名时间');
//         $classInfo['enrollEndTime'] < $this->nowDate && $this->apiReturn(1002, '报名已结束');
//         $classInfo['startTime'] < $this->nowDate && $this->apiReturn(1002, '报名时间不能超过开课时间');
        
        try {
            
            $this->db->beginTRAN();
            $userId = $this->userInfo['userId'];
            
            //检查用户是否报了该班
            $count = $this->db->getField('select count(cs_id) from tang_class_student where cs_classId="' . $classId . '" and cs_studentId="' . $this->uid . '"');
            if($count){
                throw new Exception('您已在该班级报到', -1);
            }
            
            //查核用户是否已报名
            $count = $this->db->getField('select count(tse_id) from tang_student_enroll where tse_classId="' . $classId . '" and tse_userId="' . $this->uid . '" and tse_state in (0,1)');
            if($count){
                throw new Exception('您已报名', -2);
            }
            
            $enrollId = F::getTimeMarkID();
            $time     = date('Y-m-d H:i:s');
            
            $params = array(
                'outTradeNo'    => $enrollId,
                'outCreateTime' => $time,
                'buyID'         => $this->userInfo['id'],
                'buyNick'       => $this->userInfo['nick'],
                'totalMoney'    => $classInfo['cl_cost'], 
                'totalScore'    => 0,
                'subject'       => '班级报名',
                'body'          => '用户' . $this->userInfo['nick'] . '报了班级“' . $classInfo['cl_name'] . '”',
                'showUrl'       => 'https://www.dttx.com',//暂时填写
                'dealType'      => 2,
                'returnType'    => 2
            );
            
            //生成订单
            $path = '/order/tcRegister';
            $result = $this->sdk->request($params, $path);
            
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
                'tse_userId'       => $userId,
                'tse_orderId'      => $orderId,
                'tse_userTrueName' => $this->userInfo['name'],
                'tse_certNum'      => $this->userInfo['certNum'],
                'tse_classId'      => $classId,
                'tse_fee'          => $classInfo['cl_cost'],
                'tse_payFee'       => 0.00,
                'tse_status'       => 1,
                'tse_createTime'   => $time,
                'tse_payTime'      => $time
            );
            
            $res = $this->db->insert('tang_student_enroll', $data);
            
            if(!$res){
                throw new Exception('插入报名表失败', -6);
            }
            
            $member = array('tangCollege' => $classInfo['tangCollege']);
            if($isUpdate){
                $member['level'] = $user['level'];
                $member['auth']  = $user['auth'];
                $member['authImage'] = serialize($user['au_authImg']);
            }
            $user = $this->db->update('tang_ucenter_member', $member, 'id="' . $this->uid . '"');
            if($user === false){
                throw new Exception('更新用户表失败', -7);
            }
            
            $this->db->commitTRAN();
            $this->apiReturn(1001);
        } catch (Exception $e) {
            $this->db->rollBackTRAN();
            $this->apiReturn(1002, $e->getMessage());
        }
        
    }
    
}
