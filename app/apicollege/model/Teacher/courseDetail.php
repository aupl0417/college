<?php
/**
 * @api                    {post} /Teacher/courseDetail 讲师课程详情
 * @apiDescription         讲师课程列表
 * @apiName                courseDetail
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     教师id 
   @apiParam {string}     classId    班级ID
   @apiParam {string}     courseId   课程ID
 *
 *
 *@apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        courseName: "test课程（2）",  //课程名称
        logo: "http://192.168.3.201:80/v1/tfs/T1AaCTBXAT1RCvBVdK.jpg",  //课程LOGO
        branch: "长沙分院",   //分院
        startTime: "2016-11-12",   //开课开始时间
        endTime: "2016-11-14",     //开课结束时间
        classState: "2",           //班级状态（0 报名中 1开课中 2课程结束）
        className: "test班级(4)",   //班级名称
        credit: "10",              //学分
        courseType: "test",        //课程分类
        courseGrade: "初级",        //课程等级
        hour: "10",                //课时
        descriptions: "test课程（2） 课程描述",     //课程描述
        content: "test课程（2） 课程说明",          //课程说明
        trainingSite: "308会议室",              //场地
        teachReword: "100.00",                //授课费用
        enrollCount: "1",                      //报名人数
        minutes: "600"                      //课时分钟数
    }
   }
 *
 *
 * @apiErrorExample        Error-Response:
 *{
    code: 404,
    msg: "用户id不能为空",
    data: null
  }
 *
 */
class courseDetail extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId'])   || empty($this->data['userId']))   && $this->apiReturn(404, '', '用户ID不能为空');
        (!isset($this->data['classId'])  || empty($this->data['classId']))  && $this->apiReturn(1002, '', '班级ID不能为空');
        (!isset($this->data['courseId']) || empty($this->data['courseId'])) && $this->apiReturn(1002, '',  '课程ID不能为空');
        
        $courseId = $this->data['courseId'] + 0;
        $classId = $this->data['classId'] + 0;
        
        if(!$this->uid){
            $this->apiReturn(1002, '', '教师不存在');
        }
        
        if($this->userInfo['identityType'] == 0){
            $this->apiReturn(1002, '', '您的身份不是讲师！');
        }
        
        $param = array(
            'userId'    => $this->uid,
            'courseId'     => $courseId,
            'classId'     => $classId,
        );
        
        $result = apis::request('college/api/courseDetail.json', $param, true);
        
        if($result['code'] != '1001'){
            $this->apiReturn(1002, $result['data'], $result['data']);
        }
		
		$this->apiReturn(1001, '', $result['data'], $result['data']);
    }
}
