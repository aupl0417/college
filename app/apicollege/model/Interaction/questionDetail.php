<?php
/**
 * @api                    {post} /Interaction/questionDetail 提问详情
 * @apiDescription         提问详情
 * @apiName                questionDetail
 * @apiGroup               Interaction
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {int}        id         提问ID
   @apiParam {int}        page       分页
   @apiParam {int}        pageSize   分页大小 
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        id: "11",   //提问ID
        title: "老师，课件有错误",   //提问标题
        classId: "139",
        courseId: "63",
        content: "老师，最后一页PPT里面打错了一个字。",
        username: "atestnum201",
        courseName: "大唐天下C+商业模式说明会",
        createTime: "2016-10-15 16:07:12",  //提问时间
        replyCount: 1,  //回复次数
        lists: [
            {
                id: "12",
                avatar: "http://192.168.3.201:80/v1/tfs/T1ktETB4ET1RCvBVdK.jpg",
                content: "尽职尽责",
                username: "atestnum201",
                userType: "0",   //用户类型：0 学员    1：讲师
                createTime: "2016-10-15 19:03:09"  //回复时间
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
class questionDetail extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['id'])  || empty($this->data['id']))  && $this->apiReturn(1002, '提问ID不能为空');
        
        $id = $this->data['id'] + 0;
        $page     = isset($this->data['page'])     ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        $params = array(
            'id'       => $id,
            'page'     => $page,
            'pageSize' => $pageSize
        );
//         dump($params);die;
        $res = apis::request('college/api/questionDetail.json', $params, true);
        
        if($res['code'] != '1001'){
           $this->apiReturn(1002, '', $res['data']);
        }
        
        $this->apiReturn(1001, '', $res['data']);
    }
}
