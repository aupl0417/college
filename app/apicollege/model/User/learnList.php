<?php
/**
 * @api                    {post} /User/learnList 用户学习记录
 * @apiDescription         用户学习记录（根据签到记录来判定是否上过某个课程）
 * @apiName                learnList
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
            id: "176",
            courseName: "联合代理招商会议",
            branchName: "长沙芙蓉区分院", 
            startTime: "2016-10-15"
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
class learnList extends baseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404);
        $page = isset($this->data['page']) ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $page == 0 && $this->apiReturn(1002, '分页参数非法');
        
        if(!$this->uid){
            $this->apiReturn(1002, '用户不存在');
        }
        
        $classTableIds = $this->db->getAll('select distinct(att_classTableId) from tang_attendance where att_userId="' . $this->uid . '"');
        $classTableIds = array_column($classTableIds, 'att_classTableId');
        !$classTableIds && $this->apiReturn(1002, '暂无学习记录');
        
		$field = 'cta_id as id, co_name as courseName,br_name as branchName,cta_startTime as startTime';
        $sql = 'select ' . $field . ' from tang_class_table 
                LEFT JOIN tang_class on cl_id=cta_classId 
                LEFT JOIN tang_course on co_id=cta_courseId 
                LEFT JOIN tang_branch on br_id=tangCollege 
                where cta_id in (' . implode(',', $classTableIds) .') and cta_startTime<"' . $this->nowTime .'" order by cta_id desc limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
		
		$learnList = $this->db->getAll($sql);
		!$learnList && $this->apiReturn(1002, '暂无学习记录');
		
		foreach($learnList as &$val){
		    $val['startTime'] = date('Y-m-d', strtotime($val['startTime']));
		}
		
		$this->apiReturn(1001, '', $learnList);
    }
}
