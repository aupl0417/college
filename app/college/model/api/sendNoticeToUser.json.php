<?php
/*=============================================================================
#     FileName: sendNoticeToUser.json.php
#         Desc: 站内信/推送会员
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-23 09:31:00
#      History:
#      Paramer:
=============================================================================*/
/**
 * @api                    {post} /Notice/sendNoticeToUser.json 站内信推送给会员
 * @apiDescription         站内信推送给会员
 * @apiName                sendNoticeToUser_json
 * @apiGroup               Noitce
 *
 * @apiParam {string}     userID     用户id
 * @apiParam {string}     title      标题
 * @apiParam {string}     content    内容
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
    "code": 1001,
    "msg": "推送成功",
    "data": "推送成功"
 }
 *
 */
class sendNoticeToUser_json extends api{
   function run(){
       $options = $this->options;

        $needParams = array(
            'userID'  => '会员ID',
            'title'   => '标题',
            'content' => '内容',
        );

        foreach ($needParams as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                return apis::apiCallback('1002',$needParams[$k].'错误');
            }
        }

        $params = array(
            'targets' => json_encode([$options['userID']]),
            'title'   => $options['title'],
            'content' => $options['content'],
        );

		$sdk    = new openSdk();
        $result = $sdk->request($params,'/push/allInOne');

        if ('SUCCESS_EMPTY' != $result['id']) {
            return apis::apiCallback('1002','推送失败');
        }

        return apis::apiCallback('1001','推送成功');
   }
}
