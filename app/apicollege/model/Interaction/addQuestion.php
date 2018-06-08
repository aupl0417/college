<?php
/*=============================================================================
#     FileName: addQuestion.php
#         Desc: 学生提问
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-24 16:41:04
#      History:
#      Paramer: 
=============================================================================*/
/**
 * @api                    {post} /Interaction/addQuestion 添加提问
 * @apiDescription         添加提问
 * @apiName                addQuestion
 * @apiGroup               Interaction
 *
 * @apiParam {int}        appId        设备类型 
 * @apiParam {string}     deviceID     设备id
 * @apiParam {string}     signValue    签名串
 * @apiParam {string}     userId       会员id
 * @apiParam {string}     classId      班级id
 * @apiParam {string}     courseId     课程id
 * @apiParam {string}     title        标题
 * @apiParam {string}     content      内容
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
    "data": null
 }
 *
 * @apiErrorExample     Error-Response:
 *  {
        "code": 1002,
        "msg": "提交失败",
        "data": "学员id错误"
 }
 */

class addQuestion extends baseApi{

    function __construct($options) {
        parent::__construct($options);
    }

    function run(){
        $options = $this->data;

        $needParams = array(
            'classId'  => '班级id',
            'courseId' => '课程id',
            'userId'   => '会员id',
            'title'    => '标题',
            'content'  => '内容',
        );

        foreach ($needParams as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                $this->apiReturn(1002,$v.'错误');
            }
            if($k == 'content' && mb_strlen($this->data['content'], 'utf8') > 300){
                $this->apiReturn(1002, '请限制提问内容在300个字以内');
            }
            $params[$k] = $options[$k];
        }

        $params['userId'] = $this->uid;

        $addQuestion = apis::request('/college/api/addQuestion.json', $params,true);
        if (empty($addQuestion) || 1001 != $addQuestion['code']) {
            $this->apiReturn(1002,'提交失败',$addQuestion['data']);
        }

        $this->apiReturn(1001,'', '提交成功');
    }
}
