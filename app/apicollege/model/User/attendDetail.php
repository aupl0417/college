<?php
/**
 * @api                    {post} /User/attendDetail 用户签到记录详情
 * @apiDescription         用户在某个班级的签到记录详情
 * @apiName                attendDetail
 * @apiGroup               User
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     学员id
   @apiParam {int}        classId    班级id
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            id: "251",
            attendTime: "2016-10-15 17:40:03",
            courseName: "联合代理招商会议",
            tra_name: "308会议室",
            attendState: 0,        //0：表示 签到
            address: "东六路100号有色地勘大厦",
            area: "长沙县",
        },
        {
            id: "252",
            attendTime: "2016-10-18 22:02:00",
            courseName: "联合代理招商会议",
            tra_name: 308会议室,
            attendState: 1,       //1：表示 签退
            address: "东六路100号有色地勘大厦",
            area: "长沙县",
        }
    ]
  }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: 404,
    msg: "用户id不能为空",
    data: null
  }
 *
 */
class attendDetail extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(404,'班级id不能为空');
        
        if(!$this->uid){//如果uid为空，则返回空（存在userId，但该用户未报名任何班级，签到详情显示为空）
            $this->apiReturn(1002);
        }
        
        $this->updateAttendRecord($this->uid);
        
//         $classTableIds = $this->db->getAll('select distinct(att_classTableId) from tang_attendance where att_userId="' . $this->uid . '"');
//         $classTableIds = array_column($classTableIds, 'att_classTableId');
//         !$classTableIds && $this->apiReturn(1002, '您未上过课');
        
        $classId = $this->data['classId'] + 0;
        
		$field = 'att_id as id,att_createTime as attendTime,co_name as courseName,cta_endTime as endTime,att_state as attendState,tra_name,tra_address as address,a_name as area';
		$sql = 'select ' . $field . ' from tang_attendance 
		       LEFT JOIN tang_ucenter_member on att_userId=id 
		       LEFT JOIN tang_class_table on cta_id=att_classTableId 
		       LEFT JOIN tang_class on cta_classId=cl_id 
		       LEFT JOIN tang_course on cta_courseId=co_id 
		       LEFT JOIN tang_trainingsite f on cta_trainingsiteId = tra_id 
		       LEFT JOIN tang_area on tra_areaId=a_code 
		       where att_userId="'. $this->uid . '" and cta_classId="' . $classId . '"';
		
		$Atten = $this->db->getAll($sql);
		!$Atten && $this->apiReturn(1002, '暂无签到详情');
		
// 		foreach($Atten as &$val){
// 		    if(is_null($val['attendState'])){
// 		        $attendTime = strtotime($val['attendTime']);
// 		        $startTime  = strtotime($val['startTime']);
// 		        $endTime  = strtotime($val['endTime']);
		        
// 		        if($attendTime < $startTime + 10 * 60) {
// 		            $state = 0;
// 		        }else if($attendTime > $endTime) {
// 		            $state = 1;
// 		        }
// //      		    unset($val['startTime']);
// //      		    unset($val['endTime']);
// 		        $val['attendState'] = $state;
// 		    }
		    
// 		}
		
		$this->apiReturn(1001, '', $Atten);
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
