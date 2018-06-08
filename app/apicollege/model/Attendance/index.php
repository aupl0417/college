<?php
/**
 * @api                    {post} /Attendance/index 签到接口
 * @apiDescription         学员签到
 * @apiName                index
 * @apiGroup               Attendance
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId        设备类型
   @apiParam {string}     signValue    签名串
 * @apiParam {int}        periodId     课时id
   @apiParam {string}     userId       学员id
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
		code: 215,
		msg: "打卡成功",
		data: {
			courseNmae: "联合代理招商会议",
			attendanceTime: "14:53",
			description: "1.公司简介 2.C+商业模式剖析 3.大唐天下联合代理机制 4.唐人大学分院代理机制 5.如何拓展市场"
		}
	}
 *
 */
class index extends newBaseApi{
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = time();
    }
	
	function run(){
		header("Content-type:text/html;charset=utf-8");
		$periodId = $this->data['periodId'] + 0;
		
		$begin  = 30 * 60;
		$end    = 30 * 60;
		$late   = 10 * 60;
		
		(empty($this->data['userId']) || empty($periodId)) && $this->apiReturn(200, '参数不能为空'); //用户id和课时id必填
		
		//课时信息
        $classTableInfo = $this->db->getRow('select tangCollege,cta_classId,cta_courseId from tang_class_table left join tang_class on cl_id=cta_classId where cta_id="' . $periodId . '"');

		$branchId = $classTableInfo['tangCollege'];
        $classID = $classTableInfo['cta_classId'];
		
		$uid = $this->uid;
		!$uid && $this->apiReturn(201, '用户不存在！'); //用户不存在
		
		//更新该用户或所有的签到记录
		$this->updateAttendRecord($uid);
		
		$attendStatTime = date('Y-m-d') . ' 06:00:00';
		$attendEndTime = date('Y-m-d') . ' 22:00:00';
		$sql = "select cta_classId,cta_startTime,cta_endTime from tang_class_table where cta_startTime >= '".$attendStatTime."' and cta_endTime <='".$attendEndTime."' and cta_id='".$periodId."'";
		$periodData = $this->db->getRow($sql);
		empty($periodData) && $this->apiReturn(202, '今天没有该课！');
		
		//将用户加入该课时所对应的班级
		$q = $this->db->getField("select count(cs_id) as count from tang_class_student where cs_classId='" . $periodData['cta_classId'] . "' and cs_studentId='" . $uid . "'");
		if (!$q) {
		    $this->apiReturn(210, '用户不在该班级');
		}
		
		$startTime = strtotime($periodData['cta_startTime']);
		$endTime   = strtotime($periodData['cta_endTime']);
		if(date('Y-m-d') != date('Y-m-d', $startTime)){
		    $this->apiReturn(203, '请在开课当天打卡');
		}
		if($this->nowTime + $begin < $startTime){
		    $this->apiReturn(204, '未到签到时间');
		}else if(($this->nowTime >= $startTime + $late) && ($this->nowTime <= $endTime)){//上课时间内，超过允许的迟到时间范围，设定为不能打卡
		    $this->apiReturn(204, '上课' . $late / 60 . '分钟后不能打卡');
		}else if($this->nowTime > $endTime + $end){
		    $this->apiReturn(204, '已过签到时间');
		}else if((($startTime - $this->nowTime < $begin) && ($startTime - $this->nowTime > 0)) || (($this->nowTime <= $startTime + $late))){
		    $sql = "select count(att_id) as count from tang_attendance where att_userId='" . $uid . "' and att_classTableId='" . $periodId . "'";
		    $sql .= 'and att_createTime >= "' . date('Y-m-d H:i:s', ($startTime - $begin)) . '" and att_createTime <= "' . date('Y-m-d H:i:s', $startTime + $late) . '" and att_state=0';
            $count = $this->db->getField($sql);
			//是否已经签到
		    $count && $this->apiReturn(207);
			$code = 215;
			$state = 0;
		}else if(($this->nowTime > $endTime) && ($this->nowTime < $endTime + $end)){
		    $sql = "select count(att_id) as count from tang_attendance where att_userId='" . $uid . "' and att_classTableId='" . $periodId . "'";
		    $sql .= 'and att_createTime >= "' . date('Y-m-d H:i:s', $endTime) . '" and att_createTime <= "' . date('Y-m-d H:i:s', ($endTime + $end)) . '" and att_state=1';
            $count = $this->db->getField($sql);
		    //是否已经签退
	        $count && $this->apiReturn(208);
		    $code = 214;
			$state = 1;
		}else if($this->nowTime > $endTime + $end){
		    $this->apiReturn(216, '已过签退时间');
		}
		
		$data = array(
		    'att_userId' => $uid,
		    'att_classTableId' => $periodId,
		    'att_classId'      => $classID,
		    'att_courseId'     => $classTableInfo['cta_courseId'],
		    'att_branchId'     => $branchId,
		    'att_state'        => $state,
		    'att_createTime'   => date('Y-m-d H:i:s', $this->nowTime)
		);
		$res = $this->db->insert('tang_attendance', $data);
		!$res && $this->apiReturn(208, '打卡失败');
		
		//签到/签退成功
		$this->apiReturn($code, '', $this->_ouputInfo($periodId));
	}
	
	protected function _ouputInfo($classTableId) {
	    $sql = 'select co_name,cta_description from tang_class_table left join tang_course on cta_courseId=co_id where cta_id="' . $classTableId . '"';
	    $info = $this->db->getRow($sql);
		$returnData = ['courseNmae' => $info['co_name'], 'attendanceTime' => date('H:i', $this->nowTime), 'description' => $info['cta_description'], ];
		return $returnData;
	}
	
	//更新该用户或所有的签到记录
	protected function updateAttendRecord($userId = ''){
	    
	    if(empty($userId)){
	        $where = '1';
	    }else {
	        $where = 'att_userId="' . $userId . '"';
	    }
	    
	    $attendInfo = $this->db->getAll('select att_id,att_classTableId,att_classId,att_courseId,att_createTime,cta_classId,cta_courseId,cta_startTime,cta_endTime,tangCollege from tang_attendance LEFT JOIN tang_class_table on att_classTableId=cta_id LEFT JOIN tang_class on cta_classId=cl_id where ' . $where);
        
	    if($attendInfo){
	        foreach($attendInfo as $key=>$val){
	            if(empty($val['att_classId']) && empty($val['att_courseId'])){
	                if(strtotime($val['att_createTime']) <= strtotime($val['cta_startTime']) + 600){
	                    $state = 0;
	                }else if(strtotime($val['att_createTime']) >= strtotime($val['cta_endTime'])){
	                    $state = 1;
	                }
	                
	                $data = array(
	                    'att_classId'  => $val['cta_classId'],
	                    'att_courseId' => $val['cta_courseId'],
	                    'att_branchId' => $val['tangCollege'],
	                    'att_state'    => $state
	                );
	                
	                $this->db->update('tang_attendance', $data, 'att_id="' . $val['att_id'] . '"');
	            }
	        }
	    }
	    
	    return true;
	}
}
