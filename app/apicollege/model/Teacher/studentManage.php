<?php
/**
 * @api                    {post} /Teacher/studentManage 讲师学员管理
 * @apiDescription         讲师学员管理
 * @apiName                studentManage
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型 
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     教师ID
   @apiParam {int}        classId    班级ID
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
class studentManage extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId'])  || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(1002, '班级ID不能为空');
        
        $classId = $this->data['classId'] + 0;
        $page = isset($this->data['page']) ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $page == 0 && $this->apiReturn(1002, '分页参数非法');
        
        if(!$this->uid){
            $this->apiReturn(1002, '教师不存在');
        }
        
        if($this->userInfo['identityType'] == 0){
            $this->apiReturn(1002, '您的身份不是讲师！');
        }
        
        $param = array(
//             'userId'    => $this->uid,
            'classId'     => $classId,
            'page'      => $page,
            'pageSize'  => $pageSize
        );
        
        $result = apis::request('college/api/studentManage.json', $param, true);
        
        if($result['code'] != '1001'){
            $this->apiReturn(1002, '', $result['data']);
        }
		
		$this->apiReturn(1001, '', $result['data']);
    }
}
