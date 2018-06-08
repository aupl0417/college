<?php
/**
 * @api                    {post} /User/classList 用户班级列表
 * @apiDescription         用户的班级列表
 * @apiName                classList
 * @apiGroup               User
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     学员id
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
            startTime: "11月11日",
            allowNumber: "500",
            state: "0",   //-1取消 0 报名中 1开课中 2已结束
            branchName: "长沙芙蓉区分院",
            enrollCount: "4",
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
class classList extends baseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404);
        
        if(!$this->uid){
            $this->apiReturn(1002, '用户不存在');
        }
        
        $classIds = $this->db->getAll('select cs_classId from tang_class_student where cs_studentId="' . $this->uid . '"');
        !$classIds && $this->apiReturn(601, '您还没报任何班级');
        $classIds = implode(',', array_column($classIds, 'cs_classId'));
        
        $field = 'cl_id as id, cl_name as className,cl_logo as logoUrl,cl_startTime as startTime,cl_allowableNumber as allowNumber,cl_enrolledCount as enrolledCount,cl_cost as enrollFee,cl_state as state,br_name as branchName';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_status<>-1 and tse_state<>-1) as enrollCount from tang_class
               LEFT JOIN tang_branch on br_id=tangCollege
               LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id
               where cl_status=1 and cl_state<>-1 and cl_id in (' . $classIds . ')';
        
        $classList = $this->db->getAll($sql);
        !$classList && $this->apiReturn(1002, '暂无班级信息');
        
        foreach($classList as &$val){
            $val['logoUrl'] = TFS_APIURL . '/' . $val['logoUrl'];
            $val['startTime'] = date('m月d日', strtotime($val['startTime']));
            $val['enrollInfo'] = $val['enrollCount'] + $val['enrolledCount'] > $val['allowNumber'] ? '已满员' : ($val['enrollCount'] + $val['enrolledCount']) . '/' . $val['allowNumber'] . '报名';
            unset($val['enrolledCount']);
        }
        
        $this->apiReturn(1001, '', $classList);
    }
}
