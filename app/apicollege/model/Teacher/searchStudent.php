<?php
/**
 * @api                    {post} /Teacher/searchStudent 查询学员
 * @apiDescription         讲师查询学员
 * @apiName                searchStudent
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型 
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     教师ID
   @apiParam {int}        classId    班级ID
   @apiParam {string}     keyword    关键字
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
    {
        id: "321",              //用户Id
        username: 'atestnum210', //用户名
        userId: 'd939b215b61a4cbf7afa988049d877f7',  //userId
        avatar: 'http://192.168.3.201:80/v1/tfs/T1uyhTBCDT1RCvBVdK.jpg',
        trueName: "test二一零",   //用户真实姓名
        mobile: "14412124545"   //手机号
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
class searchStudent extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId'])  || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(1002, '班级ID不能为空');
        (!isset($this->data['keyword']) || empty($this->data['keyword'])) && $this->apiReturn(1002, '请输入学员名称');
        
        $userId  = $this->data['userId'] + 0;
        $classId = $this->data['classId'] + 0;
        $keyword = trim($this->data['keyword']);
                
        if(!$this->uid){
            $this->apiReturn(1002, '教师不存在');
        }
        
        if($this->userInfo['identityType'] == 0){
            $this->apiReturn(1002, '您的身份不是讲师！');
        }
        
        $param = array(
            'userId'    => $this->uid,
            'classId'   => $classId,
            'keyword'   => $keyword,
        );
        
        $result = apis::request('college/api/searchStudent.json', $param, true);
        
        if($result['code'] != '1001'){
            $this->apiReturn(1002, '', $result['data']);
        }
		
		$this->apiReturn(1001, '', $result['data']);
    }
}
