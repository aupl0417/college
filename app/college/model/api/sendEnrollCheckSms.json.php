<?php
/*=============================================================================
#     FileName: sendEnrollCheckSms.json.php
#         Desc: 报名审核短信提醒
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-18 17:09:54
#      History:
#      Paramer:
=============================================================================*/
/**
 * @api                    {post} /api/sendEnrollCheckSms.json 获取学员信息接口
 * @apiDescription         获取学员信息接口
 * @apiName                sendEnrollCheckSms_json
 * @apiGroup               ENROLL
 * @apiPermission          wuyuanahang
 *
 * @apiParam {string}     id      报名订单编号
 *
 * @apiSuccessExample      Success-Response:
	 *	{
	 code: "1001",
	 data: {
         '发送成功'
	 }
	 }
 *
 */
class sendEnrollCheckSms_json extends api {

    function run() {
        $options = $this->options;
		if(!isset($options['id'])){
			return apis::apiCallback('1002', '报名ID参数错误');
		}

        $sql = "SELECT cl_name className,tse_createTime enrollTime,mobile,tse.tse_state FROM tang_student_enroll tse
            LEFT JOIN tang_class cl ON cl.cl_id=tse.tse_classId LEFT JOIN tang_ucenter_member um ON um.id=tse.tse_userId WHERE tse.tse_id='{$options['id']}'";

        $db = new MySql();
        $enrollInfo = $db->getRow($sql);

        if (empty($enrollInfo)) {
            return apis::apiCallback('1002', '获取报名信息错误');
        }

        if (empty($enrollInfo['mobile'])) {
            return apis::apiCallback('1002', '手机号码错误');
        }

        $tempList = array('1'=>81,'-1'=>82);

        //发送短信
        $sms = new sms();
        $sendRes = $sms->SendValidateSMS($enrollInfo['mobile'],$tempList[$enrollInfo['tse_state']],$enrollInfo);
        if (empty($sendRes)) {
            return apis::apiCallback('1002', '发送失败');
        }

        return apis::apiCallback('1001', '发送成功');

    }
}
