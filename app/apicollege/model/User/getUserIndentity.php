<?php
/*=============================================================================
#     FileName: getUserIndentity
#         Desc: 获取唐人大学的用户信息
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-22 18:28:53
#      History:
#      Paramer:
=============================================================================*/
/**
 * @api                    {post} /User/getUserIndentity 获取角色信息
 * @apiDescription         获取角色信息
 * @apiName                getUserIndentity
 * @apiGroup               User
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId        设备类型
 * @apiParam {string}     deviceID     设备id
 * @apiParam {string}     signValue    签名串
 * @apiParam {string}     signValue    签名串
 * @apiParam {string}     userId       用户id
 * 
 * @apiSuccess (Success 1001) {String} nick 会员账号
 * @apiSuccess (Success 1001) {String} levelName 等级名
 * @apiSuccess (Success 1001) {String} name 真实姓名
 * @apiSuccess (Success 1001) {String} sex 性别 0-男 1-女
 * @apiSuccess (Success 1001) {String} identityType 身份 0-学员 1-讲师
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
    "code": 1001,
    "msg": "获取成功",
    "data": {
        "nick": "mengjingqing",
        "levelName": "钻石会员",
        "name": "蒙蒙蒙",
        "level": "3",
        "te_sex": "1",
        "identityType": "1",
    }

 }
 *
 * @apiErrorExample     Error-Response:
 *  {
 *       code: 1002,
         msg: "获取失败",
         data: "获取信息错误"
 }
 */
class getUserIndentity extends baseApi{

    function __construct($options) {
        parent::__construct($options);
    }

    function run(){
        $options = $this->data;
        $baseInfo = $this->userInfo;

        if (!isset($options['userId']) || empty($options['userId'])) {
            $this->apiReturn(1002,'用户ID错误');
        }

        $params = array('userId'=>$options['userId']);
        if (1 == $this->userInfo['identityType']) {
            $teacherInfo = apis::request('/college/api/getTeacherInfo.json', $params,true);

            if (1001 != $teacherInfo['code']) {
                $this->apiReturn($teacherInfo['code'],'获取失败',$teacherInfo['data']);
            }
            $baseInfo = array_merge($teacherInfo['data'],$baseInfo);
        }

        $this->apiReturn(1001,'获取成功', $baseInfo ? $baseInfo : null);
    }
}
