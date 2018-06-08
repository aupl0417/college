<?php
/**
 * @api                    {post} /Interaction/questionList 提问列表
 * @apiDescription         提问列表
 * @apiName                questionList
 * @apiGroup               Interaction
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     用户ID
   @apiParam {int}        classId    班级ID（可选：用于班级答疑时列表）
   @apiParam {int}        userType   用户类型（0：学员；1：讲师） 
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
            id: "11",  //提问ID
            title: "老师，课件有错误",   //提问标题
            classId: "139",         //班级ID
            courseId: "63",         //课程ID
            courseName: "大唐天下C+商业模式说明会",   //课程名
            createTime: "2016-10-15 16:07:12", //提问时间
            replyCount: "1",                   //说话总次数
            lastReply: "尽职尽责",                //最后一次回复内容
            username: "atestnum201"            //最后一次回复的用户名
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
class questionList extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        $userType = isset($this->data['userType']) ? $this->data['userType']  + 0 : 1;
        $page     = isset($this->data['page'])     ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        if(!in_array($userType, array(0, 1))){
            $this->apiReturn(1002, '用户类型非法');
        }
        
        if(isset($this->data['userId']) && !$this->uid){
            $this->apiReturn(1002, '用户不存在');
        }
        
        $params = array(
            'userId'   => $this->uid,
            'userType' => $userType,
            'page'     => $page,
            'pageSize' => $pageSize
        );
        
        if(isset($this->data['classId']) && !empty($this->data['classId'])){
            $params['classId'] = $this->data['classId'] + 0;
        }
        
        $res = apis::request('college/api/questionList.json', $params, true);
        
        if($res['code'] != '1001'){
           $this->apiReturn(1002, '暂无提问', $res['data']);
        }
        
        $this->apiReturn(1001, '', $res['data']);
    }
}
