<?php
/**
 * @api                    {post} /Attendance/attendByCode 推广码签到
 * @apiDescription         推广码签到
 * @apiName                attendByCode
 * @apiGroup               Attendance
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId        设备类型
   @apiParam {string}     signValue    签名串
   @apiParam {int}        code         学员推广码
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
        code: 1001,
        msg: "签到成功",
        data: {
            userName: "atestnum201",
            className: "test班级（18）"
        }
    }
 *
 */
class attendByCode extends baseApi{
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = time();
    }
	
	function run(){
		header("Content-type:text/html;charset=utf-8");
		(!isset($this->data['code']) || empty($this->data['code'])) && $this->apiReturn('1002', '推广码不能为空');
		
		$code = $this->data['code'] + 0;//推广码
//		$this->db->delete('tang_attendance', 'att_userId in(202,110) and att_classId in (170,173)');

		$userInfo = $this->db->getRow('select id,username,avatar,authImage from tang_ucenter_member where code="' . $code . '"');
		!$userInfo && $this->apiReturn(1002, '用户不存在');

		$userId = $userInfo['id'];//用户id
		$classId = $this->db->getField('select cs_classId from tang_class_student where cs_studentId="' . $userId . '" order by cs_id desc limit 1');
		!$classId && $this->apiReturn(1002, '该用户未报任何班级');
		
		//查找通过审核的班级
		$classInfo = $this->db->getRow('select cl_name as className,tangCollege,cl_state from tang_class where cl_id="' . $classId . '" and cl_status=1 and cl_state<>-1');
		!$classInfo && $this->apiReturn(1002, '班级不存在');
		
		intval($classInfo['cl_state']) == 0 && $this->apiReturn(1002, '该班级报名中，不能签到');
		intval($classInfo['cl_state']) == 2 && $this->apiReturn(1002, '该班级已结束');
		
		$courseIds = $this->db->getAll('select cc_courseId from tang_class_course where cc_classId="' .$classId . '"');
        !$courseIds && $this->apiReturn(1002, '该班级还未排课');
		$courseIds = array_column($courseIds, 'cc_courseId');
        
		$date = date('Y-m-d', $this->nowTime);
		$endDate = $date . ' 23:59:59';
		
		$classTable = $this->db->getAll('select cta_id as cId,cta_teacherId as teacherId,cta_courseId,cta_startTime as startTime,cta_endTime as endTime,cta_trainingsiteId as siteId from tang_class_table where cta_startTime>="' . $date . '" and cta_endTime<="' . $endDate . '" and cta_classId="' . $classId . '" and cta_courseId in (' . implode(',', $courseIds) . ')');
		!$classTable && $this->apiReturn(1002, '今天没课');

		$cId = 0;
		foreach($classTable as $key=>&$val){
		    $startTime = strtotime($val['startTime']);
		    $endTime   = strtotime($val['endTime']);
		    if((($startTime - $this->nowTime < 1800) && ($startTime - $this->nowTime > 0)) || (($this->nowTime <= $startTime + 600))){
		        $cId = $val['cId'];
		    }
		}
		
		!$cId && $this->apiReturn(1002, '非签到时间');
		
		$count = $this->db->getField('select count(att_id) from tang_attendance where att_classTableId="' . $cId . '" and att_userId="' . $userId . '" and att_state=0');
		$count && $this->apiReturn(1002, '您已签到');
		
		$attend = array(
		    'att_userId'       => $userId,
		    'att_classTableId' => $cId,
		    'att_classId'      => $classId,
		    'att_courseId'     => $classTable['cta_courseId'],
		    'att_branchId'     => $classInfo['tangCollege'],
		    'att_createTime'   => date('Y-m-d H:i:s', $this->nowTime),
		    'att_state'        => 0
		);
		
		$aId = $this->db->insert('tang_attendance', $attend);
		
		!$aId && $this->apiReturn(1002, '扫码签到失败');
		
		$data = array(
		    'userName' => $userInfo['username'],
		    'className' => $classInfo['className'],
// 		    'avatar'   => $userInfo['avatar']
		);

		$this->apiReturn(1001, '签到成功', $data);
		
	}
	
}
