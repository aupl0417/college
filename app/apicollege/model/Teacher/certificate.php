<?php
/**
 * @api                    {post} /Teacher/certificate 讲师证书
 * @apiDescription         讲师证书
 * @apiName                certificate
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id 
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     用户ID
   @apiParam {int}        userType   用户类型（0：学员   1：讲师）
   @apiParam {int}        page       分页
   @apiParam {int}        pageSize   分页大小
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        count: 1,
        certList: [
            {
                id: "1",  //证书编号
                name: "初级班毕业证书",    //证书名称
                createTime: "2016-11-23 17:38:49",   //获得时间
                url: "http://192.168.3.201:80/v1/tfs/T11yETBCLT1RCvBVdK.jpg"   //证书地址
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
class certificate extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404);
        
        $page = isset($this->data['page']) ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $page == 0 && $this->apiReturn(1002, '分页参数非法');
        
        if(!$this->uid){
            $this->apiReturn(1002, '用户不存在');
        }
        
        $param = array(
            'userId'    => $this->uid,
            'userType'  => isset($this->data['userType']) ? $this->data['userType'] + 0 : 1,
            'page'      => $page,
            'pageSize'  => $pageSize
        );
        
        $result = apis::request('college/api/certificateList.json', $param, true);
        
        if($result['code'] != '1001'){
            $this->apiReturn(1002, $result['data'], $result['data']);
        }
		
		$this->apiReturn(1001, '', $result['data'], $result['data']);
    }
}
