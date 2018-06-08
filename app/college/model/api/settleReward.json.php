<?php
/**
 * @api                    {post} /api/settleReward.json 结算讲师报酬
 * @apiDescription         讲师上完一结课后结算报酬
 * @apiName                settleReward_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * @apiParam {int}        userId   教师ID
   @apiParam {int}        id       课时ID 
   @apiParam {int}        payType  支付方式
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            id: "321",
            trueName: "test二一零",
            mobile: "14412124545"
        },
        {
            id: "323",
            trueName: "个人二一二",
            mobile: "14454545598"
        },
        {
            id: "328",
            trueName: "蒙蒙蒙",
            mobile: "13622291634"
        }
    ]
   }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: '1002',
    data: '用户ID不能为空'
  }
 *
 */
class settleReward_json extends api {

    function run() {
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002', '用户ID不能为空');
        if(!isset($this->options['uid'])    || empty($this->options['uid']))    return apis::apiCallback('1002', 'ERP用户ID不能为空');
        
        $userId   = $this->options['userId']  + 0;
        $uid      = trim($this->options['uid']);
                
        $db = new MySql();
        $field = 'cta_id as classTableId,te_courseReward as courseReward,1 as num,co_name as courseName,cta_startTime as startTime,cta_endTime as endTime,username,trueName,userId,cta_teacherId as teacherId,id';
        $sql = 'select ' . $field . ' from tang_class_table 
                LEFT JOIN tang_ucenter_member on cta_teacherId=id 
                LEFT JOIN tang_course on co_id=cta_courseId 
                LEFT JOIN tang_teacher on te_userId=cta_teacherId 
            where cta_payState=0 and cta_teacherId="' . $userId . '" and cta_endTime<="' . date('Y-m-d H:i:s') . '" order by cta_id asc';
        
        $classTableList = $db->getAll($sql);
        
        if(!$classTableList){
            return apis::apiCallback('1001');
        }
        
        $sdk  = new openSdk();
        try {
            $db->beginTRAN();
            foreach($classTableList as $key=>$val){
                $incomeId = F::getTimeMarkID();
                $time = $val['endTime'];
                
                $params = array(
                    'outTradeNo'    => $incomeId,
                    'outCreateTime' => $time,
                    'buyID'         => $val['userId'],
                    'buyNick'       => $val['username'],
                    'totalMoney'    => $val['courseReward'],
                    'totalScore'    => 0,
                    'subject'       => '讲师课时结算',
                    'body'          => '讲师' . $val['username'] . '获得金额' . $val['courseReward'] . '元',
                    'showUrl'       => 'https://www.dttx.com',//暂时填写
                    'dealType'      => 2,
                    'returnType'    => 2,
                    'payType'       => 1
                );
                
                //生成订单
                $path = '/order/tcRegister';
                
                $result = $sdk->request($params, $path);
            
                if(!is_array($result)){
                    throw new Exception($result, -3);
                }
                
                if($result['id'] != 'SUCCESS' && $result['id'] != 'SUCCESS_EMPTY'){
                    throw new Exception($result['msg'], -4);
                }
                
                $orderId = $result['info']['order_id'];//获取订单号
                
                if(!$orderId){
                    throw new Exception('获取订单号失败', -5);
                }
                
                $data = array(
                    'tti_id'            => $incomeId,
                    'tti_userId'        => $val['id'],
                    'tti_uid'           => $val['userId'],
                    'tti_teacherName'   => $val['trueName'],
                    'tti_courseName'    => $val['courseName'],
                    'tti_classTableId'  => $val['classTableId'],
                    'tti_num'           => $val['num'],
                    'tti_fee'           => $val['courseReward'],
                    'tti_total'         => sprintf("%.2f", $val['num'] * $val['courseReward']),
                    'tti_orderId'       => $orderId,
                    'tti_payType'       => $params['payType'],
                    'tti_createTime'    => $time
                );
                
                $res = $db->insert('tang_teacher_income', $data);
                
                if(!$res){
                    throw new Exception('插入讲师收益详细表失败', -6);
                }
                
                $upClassTable = $db->update('tang_class_table', ['cta_payState' => 1], 'cta_id="' . $val['classTableId'] . '"');
                if(!$upClassTable){
                    throw new Exception('更新课时表状态失败', -7);
                }
            }
            
            $db->commitTRAN();
            return apis::apiCallback(1001);
        } catch (Exception $e) {
            $db->rollBackTRAN();
            return apis::apiCallback(1002, $e->getMessage());
        }
        
    }
}
