<?php
/**
 * @api                    {post} /Teacher/courseList 讲师课程列表
 * @apiDescription         讲师课程列表
 * @apiName                courseList
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型 
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     教师id
   @apiParam {int}        state      班级状态（0 报名中; 1开课中; 2课程结束; 3全部）
   @apiParam {int}        page       分页
   @apiParam {int}        pageSize   分页大小
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            id: "67",
            courseName: "test课程（2）",   //班级名称
            courseLogo: "http://192.168.3.201:80/v1/tfs/T1AaCTBXAT1RCvBVdK.jpg",   //班级LOGO
            branchName: "长沙分院",     //分院名称
            classState: "0",         //班级状态（0 报名中 1开课中 2课程结束）
            classId: "123",          //班级ID
            startTime: "2016-11-12",  //开课时间
            enrollCount: "1"          //报名人数
        },
        {
            id: "58",
            courseName: "市场运营",
            courseLogo: "http://192.168.3.201:80/v1/tfs/T1ttCTBXAT1RCvBVdK.png",
            branchName: "长沙分院",
            classState: "0",         //班级状态（0 报名中 1开课中 2课程结束）
            classId: "126",
            startTime: "2016-11-14",
            enrollCount: "3"
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
class courseList extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404, '', '');
        $state = isset($this->data['state']) ? $this->data['state'] + 0 : 3;
        $page = isset($this->data['page']) ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $page == 0 && $this->apiReturn(1002, '', '分页参数非法');
        
        if(!$this->uid){
            $this->apiReturn(1002, '', '教师不存在');
        }
        
        if($this->userInfo['identityType'] == 0){
            $this->apiReturn(1002, '', '您的身份不是讲师！');
        }
        
        $param = array(
            'userId'    => $this->uid,
            'state'     => $state,
            'page'      => $page,
            'pageSize'  => $pageSize
        );

        $result = apis::request('college/api/teacherCourseList.json', $param, true);
        
        if($result['code'] != '1001'){
            $this->apiReturn(1002, '', $result['data']);
        }
		
		$this->apiReturn(1001, '', $result['data']);
    }
}
