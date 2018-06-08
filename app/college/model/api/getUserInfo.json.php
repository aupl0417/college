<?php
 /**
 * @api                    {post} /api/getUserInfo.json 获取学员信息接口
 * @apiDescription         获取学员信息
 * @apiName                getUserInfo.json
 * @apiGroup               学员
 * @apiPermission          aupl 
 *
 * @apiParam {string}     userId      用户id		userId,userName,mobile不能同时为空
 *
 *
 * @apiSuccessExample      Success-Response:
	 *	{
    code: "1001",
    data: {
    userId: "261",
    nick: "aupl0401",
    name: "aupl",
    id: "f6223978a918351691297251738ecdd1",
    certNum: "4310251933305671636",
    level: "1",
    auth: "1110",
    tel: '1440001234',
    avatar: 'http://192.168.3.201:80/v1/tfs/T1ktETB4ET1RCvBVdK.jpg',
    email: '770517692@qq.com',
    type: 0
    }
    }
 *
 */
class getUserInfo_json extends api{
	
    private $db;
	
    function run() {
        
        //验证参数是否存在
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002','用户id为空');
        
		$this->db = new MySql();
		$userInfo = array();
	    $userInfo = $this->db->getRow('select id as userId,username as nick,trueName as name,userId as id,identityType,certNum,level,auth,avatar,mobile as tel,email,type,authImage,mobile,code from tang_ucenter_member where userId="' . $this->options['userId'] . '"');
	    if(!$userInfo){
	        return apis::apiCallback('1002', '用户不存在');
	    }
		
		return apis::apiCallback('1001', $userInfo);
    }
	
}
