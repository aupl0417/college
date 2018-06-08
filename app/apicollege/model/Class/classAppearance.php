<?php
/*=============================================================================
#     FileName: classAppearance.php
#         Desc: 班级学员风采
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-25 10:02:43
#      History:
#      Paramer:
=============================================================================*/
/**
 * @api                    {post} /api/classAppearance 获取学员风采
 * @apiDescription         获取学员风采
 * @apiName                classAppearance
 * @apiGroup               Class
 *
 * @apiParam {int}     classId     班级id
 *
 * @apiSuccessExample      Success-Response:
 *	{
    "code": 1001,
    "msg" : '获取成功',
    "data": [

    ]
 }
 *
 */
class classAppearance extends baseApi{
   function run(){
       $options = $this->data;
       $appearance = apis::request('college/api/classAppearance.json',$options,true);

       if (1001 != $appearance['code']) {
           $this->apiReturn(1002, '错误','获取学员风采信息失败');
       }

       foreach ($appearance['data'] as $k=>$v) {
           if (empty($v['classPictures'])) {
               unset($appearance['data'][$k]);
           }
       }

       $this->apiReturn(1001, '获取成功', $appearance['data']);
   }
}
