<?php
/**
 * @api                    {post} /api/incomeDetail.json 教师收益详情
 * @apiDescription         教师收益详情
 * @apiName                incomeDetail_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
 * @apiParam {int}        appId      设备类型 
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     教师ID
   @apiParam {string}     id         收益ID
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        id: "2016112314570645482963140",          //收益编号/收益ID
        courseName: "test课程（2）",                //课程名称
        total: "100.00",                         //收益金额
        startTime: "2016-11-14 16:30:00~16:50",  //上课时间
        createTime: "2016-11-14 16:50:00",       //结算时间
        payType: "1",                            //支付方式（1：余额，2：唐宝，3：现金）
        type: "1"                                //收益类型 （1：授课费）
    }
  }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: '1002',
    data: '用户ID不能为空'
  }
 *
 */
class incomeDetail_json extends api {

    function run() {
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002', '用户ID不能为空');
        if(!isset($this->options['id'])     || empty($this->options['id']))   return apis::apiCallback('1002', '收益ID不能为空');
        
        $userId   = $this->options['userId'] + 0;
        $id       = trim($this->options['id']);
        
        $db = new MySql();
        $field = 'tti_id as id,tti_courseName as courseName,tti_total as total,cta_startTime as startTime,cta_endTime as endTime,tti_createTime as createTime,tti_payType as payType,1 as type';
        $sql   = 'select ' . $field . ' from tang_teacher_income 
                 LEFT JOIN tang_class_table on cta_id=tti_classTableId 
                where tti_userId="' . $userId . '" and tti_id="' . $id . '"';
        
        $incomeData = $db->getRow($sql);
        
        if(!$incomeData){
            return apis::apiCallback('1002', '无该收益信息');
        }
        
        $incomeData['startTime'] = $incomeData['startTime'] . '~' . date('H:i', strtotime($incomeData['endTime']));
        unset($incomeData['endTime']);
        
        if(!$incomeData){
            return apis::apiCallback('1002', '暂无收益信息');
        }
        
        
        apis::apiCallback('1001', $incomeData);
    }
}
