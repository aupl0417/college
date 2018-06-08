<?php
/*=============================================================================
#     FileName: addInteration.php
#         Desc: 添加留言
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-24 15:22:20
#      History:
#      Paramer: 
=============================================================================*/
/**
 * @api                    {post} /Interaction/addInteration 添加留言
 * @apiDescription         添加留言
 * @apiName                addInteration
 * @apiGroup               Interaction 
 *
 * @apiParam {int}        appId        设备类型
 * @apiParam {string}     deviceID     设备id
 * @apiParam {string}     userId       会员id
 * @apiParam {string}     signValue    签名串
 * @apiParam {string}     classId      班级id
 * @apiParam {string}     courseId     课程id
 * @apiParam {string}     content      内容
 * @apiParam {string}     interationParentId 上一个留言的ID
 *
 * @apiSuccess (Success 1001) {Int} code 状态
 * @apiSuccess (Success 1001) {Int} msg  提示信息
 * @apiSuccess (Success 1001) {Int} data 数据
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    "code": 1001,
    "msg": "提交成功",
    "data":null
 }
 *
 * @apiErrorExample     Error-Response:
 *  {
        "code": 1002,
        "msg": "提交失败",
        "data": "学员id错误"
 }
 */

class addInteration extends baseApi{

    function __construct($options) {
        parent::__construct($options);
    }

    function run(){
        $options = $this->data;

        $needParams = array(
            'classId'  => '班级id',
            'courseId' => '课程id',
            'userId'   => '会员id',
            'content'  => '内容',
        );

        foreach ($needParams as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                $this->apiReturn(1002, '', $v.'错误');
            }
            if($k == 'content' && mb_strlen($this->data['content'], 'utf8') > 300){
                $this->apiReturn(1002, '请限制回复内容在300个字以内');
            }
            $params[$k] = $options[$k];
        }

        if (isset($options['interationParentId']) && !empty($options['interationParentId'])) {
            $params['interationParentId'] = intval($options['interationParentId']);
        }

        $params['userId']   = $this->uid;
        $params['userType'] = $this->userInfo['identityType'];
        
        $addInterationRes = apis::request('/college/api/addInteration.json', $params,true);
        if (empty($addInterationRes) || 1001 != $addInterationRes['code']) {
            $this->apiReturn(1002,'提交失败',$addInterationRes['data']);
        }

        $this->apiReturn(1001, '', '提交成功');
    }
}
