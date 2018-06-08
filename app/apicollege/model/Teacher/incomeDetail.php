<?php
/**
 * @api                    {post} /Teacher/incomeDetail 讲师收益详情
 * @apiDescription         讲师收益详情
 * @apiName                incomeDetail
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型 
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     教师ID
   @apiParam {string}     id         收益ID
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        id: "2016112314570645482963140",          //收益编号/收益ID
        courseName: "test课程（2）",                //课程名称
        total: "100.00",                         //收益金额
        startTime: "2016-11-14 16:30:00~16:50",  //上课时间
        createTime: "2016-11-14 16:50:00",       //结算时间
        payType: "1",                            //支付方式（1：余额，2：唐宝，3：现金）
        type: "1"                                //收益类型 （1：授课费）
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
class incomeDetail extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['id'])   || empty($this->data['id']))  && $this->apiReturn(1002, '收益ID不能为空');
        
        $id  = trim($this->data['id']); //收益ID
        
        if(!$this->uid){
            $this->apiReturn(1002, '教师不存在');
        }
        
        if($this->userInfo['identityType'] == 0){
            $this->apiReturn(1002, '您的身份不是讲师！');
        }
        
        $params = array(
            'userId' =>$this->uid,
            'id'     =>$id
        );
        
        $res = apis::request('college/api/incomeDetail.json', $params, true);
        
        if($res['code'] != '1001'){
            $this->apiReturn(1002, '', $res['data']);
        }
        
        $this->apiReturn(1001, '', $res['data']);
    }
}
