<?php
/*=============================================================================
#     FileName: enrollReviewNotice.json.php
#         Desc: 报名审核结果站内信推送
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-23 11:01:58
#      History:
#      Paramer:
=============================================================================*/
/**
 * @api                    {post} /api/enrollReviewNotice.json 报名审核结果站内信推送
 * @apiDescription         报名审核结果站内信推送
 * @apiName                enrollReviewNotice_json
 * @apiGroup               报名
 *
 * @apiParam {string}     tseID    报名ID
 * @apiParam {string}     state    审核结果 1 通过 -1 拒绝
 *
 * @apiSuccessExample     Success-Response:
 *	{
    "code": 1001,
    "msg": "推送成功",
    "data": "推送成功"
 }
 *
 */

class enrollReviewNotice_json extends api{
   function run(){
       $options = $this->options;

        $needParams = array(
            'tseID'  => '报名ID',
            'state'  => '结果',
        );

        foreach ($needParams as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                return apis::apiCallback('1002',$needParams[$k].'错误');
            }
        }

        $sql = "SELECT cl_name className,tse_createTime enrollTime,um.userId
            FROM tang_student_enroll tse LEFT JOIN tang_class cl ON cl.cl_id=tse.tse_classId LEFT JOIN tang_ucenter_member um ON um.id=tse.tse_userId
            WHERE tse.tse_id='{$options['tseID']}'";

        $db = new MySql();
        $enrollInfo = $db->getRow($sql);

        if (empty($enrollInfo)) {
            return apis::apiCallback('1002', '获取报名信息错误');
        }

        $noticeParam = array(
            'userID' => $enrollInfo['userId'],
            'title'  => '报名审核结果',
        );

        $content = "【大唐天下】亲！%s 您在%s报名的《%s》课程，%s。详见【唐人大学-班级管理】。客服：95083";
        if (1 == $options['state']) {
            $noticeParam['content'] = sprintf($content,'',$enrollInfo['enrollTime'],$enrollInfo['className'],'已经通过审核');
        }else{
            $noticeParam['content'] = sprintf($content,'很抱歉！',$enrollInfo['enrollTime'],$enrollInfo['className'],'未能通过审核');
        }

        $res = apis::request('/college/api/sendNoticeToUser.json',$noticeParam,true);

        if (1001 != $res['code']) {
            return apis::apiCallback('1001','推送失败');
        }

        return apis::apiCallback('1001','推送成功');
   }
}
