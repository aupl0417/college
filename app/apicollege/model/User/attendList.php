<?php
/**
 * @api                    {post} /User/attendList 用户签到记录
 * @apiDescription         用户签到记录
 * @apiName                attendList
 * @apiGroup               User
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     学员id
   @apiParam {string}     page       分页id
   @apiParam {string}     pageSize   分页大小
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            id: "2",
            className: "201610基础课程",
            logoUrl: "https://image.dttx.com/v1/tfs/T1v.KTB5KT1RCvBVdK.jpg",
            startTime: "2016-11-11",
            allowNumber: "500",
            enrollFee: "6000.00",
            state: "0",   //状态 -1 取消     0: 报名中 1 : 开课中 2:已结束
            branchName: "长沙芙蓉区分院",
            enrollCount: "2",
            enrollInfo: "4/500报名"
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
class attendList extends baseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404);
        $page = isset($this->data['page']) ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $page == 0 && $this->apiReturn(1002, '分页参数非法');
        
        if(!$this->uid){//如果uid为空，则返回空（存在userId，但该用户未报名任何班级，签到列表显示为空）
            $this->apiReturn(1002, '用户不存在', '用户不存在');
        }
        
        $classTableIds = $this->db->getAll('select distinct(att_classTableId) from tang_attendance where att_userId="' . $this->uid . '"');
        $classTableIds = array_column($classTableIds, 'att_classTableId');
        !$classTableIds && $this->apiReturn(1002, '暂无签到记录', '暂无签到记录');
        
        $classIds = $this->db->getAll('select distinct(cta_classId) from tang_class_table where cta_id in (' . implode(',', $classTableIds) .')');
        $classIds = array_column($classIds, 'cta_classId');
        !$classIds && $this->apiReturn(1002, '您未报任何班级', '您未报任何班级');
        
		$field = 'cl_id as id, cl_name as className,cl_logo as logoUrl,cl_startTime as startTime,cl_allowableNumber as allowNumber,cl_enrolledCount as enrolledCount,cl_cost as enrollFee,cl_state as state,br_name as branchName';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_status<>-1 and tse_state<>-1) as enrollCount from tang_class 
                LEFT JOIN tang_branch on br_id=tangCollege
                LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id
                where cl_id in (' . implode(',', $classIds) .') limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
		
		$attenList = $this->db->getAll($sql);
		!$attenList && $this->apiReturn(1002, '暂无签到记录');
		
		foreach($attenList as &$val){
		    $val['logoUrl'] = TFS_APIURL . '/' . $val['logoUrl'];
		    $val['enrollInfo'] = $val['enrollCount'] + $val['enrolledCount'] > $val['allowNumber'] ? '已满员' : ($val['enrollCount'] + $val['enrolledCount']) . '/' . $val['allowNumber'] . '报名';
		    unset($val['enrolledCount']);
		}
		
		$this->apiReturn(1001, '', $attenList);
    }
}
