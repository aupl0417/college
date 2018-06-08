<?php
/**
 * @api                    {post} /Teacher/courseTable 讲师备课表
 * @apiDescription         讲师备课表
 * @apiName                courseTable
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id 
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     教师ID
   @apiParam {int}        year       年
   @apiParam {int}        month      月
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            day: 1,
            courseCount: 0
        },
        {
            day: 2,
            courseCount: 0
        },
        {
            day: 3,
            courseCount: 0
        },
        {
            day: 4,
            courseCount: 0
        },
        {
            day: 5,
            courseCount: 0
        },
        {
            day: 6,
            courseCount: 0
        },
        {
            day: 7,
            courseCount: 0
        },
        {
            day: 8,
            courseCount: 0
        },
        {
            day: 9,
            courseCount: 0
        },
        {
            day: 10,
            courseCount: 0
        },
        {
            day: 11,
            courseCount: 0
        },
        {
            day: 12,
            courseCount: 0
        },
        {
            day: 13,
            courseCount: 0
        },
        {
            day: 14,
            courseCount: "2节课"
        },
        {
            day: 15,
            courseCount: "1节课"
        },
        {
            day: 16,
            courseCount: 0
        },
        {
            day: 17,
            courseCount: "4节课"
        },
        {
            day: 18,
            courseCount: 0
        },
        {
            day: 19,
            courseCount: 0
        },
        {
            day: 20,
            courseCount: "14节课"
        },
        {
            day: 21,
            courseCount: "1节课"
        },
        {
            day: 22,
            courseCount: 0
        },
        {
            day: 23,
            courseCount: 0
        },
        {
            day: 24,
            courseCount: 0
        },
        {
            day: 25,
            courseCount: 0
        },
        {
            day: 26,
            courseCount: 0
        },
        {
            day: 27,
            courseCount: 0
        },
        {
            day: 28,
            courseCount: 0
        },
        {
            day: 29,
            courseCount: 0
        },
        {
            day: 30,
            courseCount: 0
        }
    ]
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
class courseTable extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId'])  || empty($this->data['userId']))  && $this->apiReturn(404, '', '用户ID不能为空');
        (!isset($this->data['year'])  || empty($this->data['year']))  && $this->apiReturn(1002, '', '请选择年份');
        (!isset($this->data['month']) || empty($this->data['month'])) && $this->apiReturn(1002, '', '请选择月份');
        
        if(!$this->uid){
            $this->apiReturn(1002, '教师不存在');
        }
        
        if($this->userInfo['identityType'] == 0){
            $this->apiReturn(1002, '您的身份不是讲师！');
        }
        
        $classId = $this->data['userId'] + 0;
        $year    = $this->data['year']  + 0;
        $month   = $this->data['month'] + 0;
        
        $param = array(
            'userId'    => $this->uid,
            'year'      => $year,
            'month'     => $month
        );
        
        $result = apis::request('college/api/courseTable.json', $param, true);
        
        if($result['code'] != '1001'){
            $this->apiReturn(1002, $result['data']);
        }
		
		$this->apiReturn(1001, '', $result['data']);
    }
}
