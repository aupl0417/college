<?php
/*=============================================================================
#     FileName: getCourseList.php
#         Desc: 通过班级ID获取课程列表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-29 20:33:02
#      History:
#      Paramer: 
=============================================================================*/
/**
 * @api                    {post} /Course/getCourseList 通过班级ID获取课程列表
 * @apiDescription         通过班级ID获取课程列表
 * @apiName                getCourseList
 * @apiGroup               Course
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId        设备类型
 * @apiParam {string}     deviceID     设备id
 * @apiParam {string}     signValue    签名串
 * @apiParam {string}     classId      班级id(可选)
 *
 *
 * @apiSuccess (Success 1001) {Int} co_id 课程ID
 * @apiSuccess (Success 1001) {String} co_name 课程名
 *
 * @apiSuccessExample      Success-Response:
 *{
    "code": 1001,
    "msg": "获取成功",
    "data":
    [
        {
            "co_id": "57",
            "co_name": "大唐天下C 商业模式"
        },
        {
            "co_id": "59",
            "co_name": "电子商务"
        }
   ]
 }
 *
 * @apiErrorExample     Error-Response:
 *  {
 *       code: 1002,
         msg: "获取失败",
         data: "没有课程列表"
 }
 */
class getCourseList extends baseApi{

    function __construct($options) {
        parent::__construct($options);
    }

    function run(){
        $options = $this->data;

        $params = array();
        if (isset($options['classId']) && !empty($options['classId'])) {
            $params['classId'] = $options['classId'];
        }

        $courseList = apis::request('/college/api/getCourseList.json', $params,true);

        if (1001 != $courseList['code']) {
            $this->apiReturn(1001,'获取失败', '没有课程列表');
        }

        $this->apiReturn(1001,'获取成功', $courseList['data']);
    }
}
