<?php
/**
 * @api                    {post} /User/enrollDetail 报名详情
 * @apiDescription         用户所报的班级的报名详情
 * @apiName                enrollDetail
 * @apiGroup               User
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     学员id
   @apiParam {string}     classId    班级id
   @apiParam {string}     enrollId   订单id
 *
 *
 * @apiSuccessExample      Success-Response:
 * {
        code: 1001,
        msg: "操作成功",
        data: {
            id: "2016102317573048418887585",   //异动id
            orderId: "2016102317572903864429084",  //订单id
            enrollState: "1",    //审核状态   0：初始；1：审核通过；-1：已拒绝
            enrollStatus: "2",   //订单状态    付款状态  0：未付款；1：已付款；2：已报到；3：已转人；-1：订单已取消
            tse_reason: "同意",   //审核原因
            className: "201610基础课程",   //班级名称
            classLogo: "https://image.dttx.com/v1/tfs/T1v.KTB5KT1RCvBVdK.jpg",
            branchName: "长沙芙蓉区分院",    //所属分院
            enrollFee: "6000.00",       //报名费
            enrollTime: "2016-10-23 17:57:30",   //报名时间
            checkTime: "2016-10-24 16:48:15"     //审核时间
        }
    }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: 404,
    msg: "用户id不能为空",
    data: null
  }
 *
 */
class enrollDetail extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(1003, '班级id不能为空');
        (!isset($this->data['enrollId']) || empty($this->data['enrollId'])) && $this->apiReturn(1003, '报名订单id不能为空');
        
        if(!$this->uid){
            $this->apiReturn(1002, '用户不存在');
        }
        
        $classId  = $this->data['classId'] + 0;
        $enrollId = $this->data['enrollId'];
        $field = 'tse_id as id,tse_orderId as orderId,tse_state as enrollState,tse_status as enrollStatus,tse_reason,cl_name as className,cl_logo as classLogo,br_name as branchName,tse_fee as enrollFee,tse_createTime as enrollTime,tse_eTime as checkTime';
        $sql = 'select ' . $field . ' from tang_student_enroll 
               LEFT JOIN tang_class on cl_id=tse_classId 
               LEFT JOIN tang_branch on br_id=tangCollege 
               where cl_status=1 and tse_status<>-1 and tse_userId="' . $this->uid .'" and tse_classId="' . $classId . '" and tse_id="' . $enrollId . '"';
        
        $enrollData = $this->db->getRow($sql);
        !$enrollData && $this->apiReturn(1002, '暂无报名信息');
        
        $enrollData['classLogo'] = TFS_APIURL . '/' . $enrollData['classLogo'];
        
        $this->apiReturn(1001, '', $enrollData);
    }
}
