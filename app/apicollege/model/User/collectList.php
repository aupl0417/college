<?php
/**
 * @api                    {post} /User/collectList 用户班级收藏列表
 * @apiDescription         用户班级收藏列表
 * @apiName                collectList
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
            startTime: "11月11日",
            allowNumber: "500",    //班级最大报名人数
            branchName: "长沙芙蓉区分院",
            enrollFee: "6000.00",  //报名费
            enrollCount: "3",    //班级当前报名人数
            enrollStatus: 1,    //用户对应该班级是否报名 0：未报名；1：已报名      根据报名订单的审核通过就为1，其它都为0
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
class collectList extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404);
        $page = isset($this->data['page']) ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $page == 0 && $this->apiReturn(1002, '分页参数非法');
        
        if(!$this->uid){
            $this->apiReturn(1002, '用户不存在');
        }
        
        $field = 'cl_id as id,cl_name as className,cl_logo as logoUrl,cl_startTime as startTime,cl_allowableNumber as allowNumber,cl_enrolledCount as enrolledCount,cl_cost as enrollFee,br_name as branchName';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_status<>-1 and tse_state<>-1) as enrollCount from tang_class_collection 
               LEFT JOIN tang_class on cl_id=tcc_classId 
               LEFT JOIN tang_branch on br_id=tangCollege 
               LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id 
               where cl_state in(0,1,2) and cl_status=1 and tcc_userId="' . $this->uid .'" order by tcc_createTime desc limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        
        $classList = $this->db->getAll($sql);
        !$classList && $this->apiReturn(1002, '您还没有收藏班级');
        
        foreach($classList as &$val){
            $val['logoUrl'] = TFS_APIURL . '/' . $val['logoUrl'];
            $val['startTime'] = date('m月d日', strtotime($val['startTime']));
            $val['enrollInfo'] = $val['enrollCount'] + $val['enrolledCount'] > $val['allowNumber'] ? '已满员' : ($val['enrollCount'] + $val['enrolledCount']) . '/' . $val['allowNumber'] . '报名';
            unset($val['enrolledCount']);
            $status = $this->db->getField('select tse_status from tang_student_enroll where tse_classId="' . $val['id'] . '" and tse_userId="' . $this->uid . '" and tse_state=1');
            if(!$status){
                $val['enrollStatus'] = 0;
            }else {
                $val['enrollStatus'] = 1;
            }
        }
        
        $this->apiReturn(1001, '', $classList);
    }
}
