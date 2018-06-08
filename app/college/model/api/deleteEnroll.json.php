<?php
/* 取消报名接口
 * @param id       type : string 报名订单id   must
 * @author aupl
 * */
 
 /**
 * @api                    {post} /api/deleteEnroll.json 报名业务接口
 * @apiDescription         取消学员报名
 * @apiName                deleteEnroll.json
 * @apiGroup               学员
 * @apiPermission          aupl 
 *
 * @apiParam {int}        id       报名订单id
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
class deleteEnroll_json extends api{
    private $db;
	
    function run() {
		$this->db = new MySql();
		
        //验证参数是否存在
//        if(!isset($this->options['id']) || empty($this->options['id'])) return apis::apiCallback('1002','报名订单id为空');
		
		$id = $this->options['id'];
		$where = '1';
		if(isset($this->options['classId']) && !empty($this->options['classId'])){
		    $where .= ' and tse_classId="' . intval($this->options['classId']) . '"';
		}
		
		if(isset($this->options['userId']) && !empty($this->options['userId'])){
		    $where .= ' and tse_userId="' . intval($this->options['userId']) . '"';
		}
		
		if(isset($this->options['id']) && !empty($this->options['id'])){
		    $where .= ' and tse_id="' . $this->options['id'] . '"';
		}
		
		$count = $this->db->getField('select count(tse_id) from tang_student_enroll where ' . $where);
		
		if(!$count){
		    return apis::apiCallback('1002', '没有该报名记录');
		}
		
		$res = $this->db->update('tang_student_enroll', ['tse_status' => -1], $where);
		
		if($res === false) return apis::apiCallback('1002', '取消订单失败');
		
		return apis::apiCallback('1001', '取消订单成功');
    }
	
}
