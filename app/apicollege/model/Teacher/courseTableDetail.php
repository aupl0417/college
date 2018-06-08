<?php
/**
 * @api                    {post} /Teacher/courseTableDetail 讲师备课表详情
 * @apiDescription         讲师备课表详情
 * @apiName                courseTableDetail
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id 
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     教师ID
   @apiParam {int}        year       年
   @apiParam {int}        month      月
   @apiParam {int}        day        日
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        week: "2016-11-17 星期四",
        course: [
            {
                className: "test班级(13)",
                courseName: "线性代数20",
                startTime: "10:00",
                endTime: "11:00"
            },
            {
                className: "test班级（14）",
                courseName: "高等数学",
                startTime: "10:00",
                endTime: "11:00"
            }
        ]
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
class courseTableDetail extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId'])|| empty($this->data['userId'])) && $this->apiReturn(404);
        (!isset($this->data['year'])  || empty($this->data['year']))   && $this->apiReturn(1002, '请选择年份');
        (!isset($this->data['month']) || empty($this->data['month']))  && $this->apiReturn(1002, '请选择月份');
        (!isset($this->data['day'])   || empty($this->data['day']))    && $this->apiReturn(1002, '请选择天数');
        
        if(!$this->uid){
            $this->apiReturn(1002, '教师不存在');
        }
        
        if($this->userInfo['identityType'] == 0){
            $this->apiReturn(1002, '您的身份不是讲师！');
        }
        
        $classId = $this->data['userId'] + 0;
        $year    = $this->data['year']   + 0;
        $month   = $this->data['month']  + 0;
        $day     = $this->data['day']    + 0;
        
        $param = array(
            'userId'    => $this->uid,
            'year'      => $year,
            'month'     => $month,
            'day'       => $day
        );
        
        $result = apis::request('college/api/courseTableDetail.json', $param, true);
        
        if($result['code'] != '1001'){
            $this->apiReturn(1002, '', $result['data']);
        }
		
		$this->apiReturn(1001, '', $result['data']);
    }
}
