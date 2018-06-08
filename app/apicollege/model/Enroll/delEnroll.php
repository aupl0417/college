<?php
/* 报名接口
 * @param $uname   type : string 用户id   must
 * @param $payType type : int    支付方式      默认 ：0
 * @param $classId type : int    班级id   must
 * @author aupl
 * */
 
 /**
 * @api                    {post} /Enroll/delEnroll 删除报名接口 --已废弃
 * @apiDescription         删除学员报名
 * @apiName                delEnroll
 * @apiGroup               Enroll
 * @apiPermission          aupl 
 *
 * @apiParam {int}        appId        设备类型
   @apiParam {string}     deviceID     设备id
   @apiParam {string}     signValue    签名串
 * @apiParam {int}        classId      课时id
   @apiParam {string}     userId       用户id
   @apiParam {string}     enrollId     报名订单id
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
        code: 1001, 
        msg: "删除成功",
        data: null
    }
 *
 */
class delEnroll extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        //验证参数是否存在
        (!isset($this->data['userId'])  || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(502);
        (!isset($this->data['enrollId']) || empty($this->data['enrollId'])) && $this->apiReturn(1002, '报名订单id不能为空');
        
        $classId  = $this->data['classId'] + 0;
        $enrollId = $this->data['enrollId'];
        
        $where = 'tse_userId="' . $this->uid . '" and tse_classId="' . $classId . '" and tse_id="'. $enrollId . '"';
        $count = $this->db->getField('select count(tse_id) from tang_student_enroll where ' . $where);
        !$count && $this->apiReturn(1002, '没有该报名记录', '没有该报名记录');
        
        $res = $this->db->update('tang_student_enroll', array('tse_status' => -1), $where);
        $res === false && $this->apiReturn(1002, '', '删除失败');
        
        $this->apiReturn(1001, '', '删除成功');
    }
    
}
