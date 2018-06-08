<?php
/**
 * @api                    {post} /User/enrollList 用户报名管理
 * @apiDescription         用户所报的班级列表
 * @apiName                enrollList
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
            tse_userId: "268",
            enrollId: "2016102317573048418887585",  //报名id
            enrollState: "0",     当前报名状态： 0：初始；1：审核通过；-1：已拒绝
            enrollStatus: "0",    //付款状态  0：未付款；1：已付款；2：已报到；3：已转人；-1：订单已取消
            className: "201610基础课程",
            logoUrl: "https://image.dttx.com/v1/tfs/T1v.KTB5KT1RCvBVdK.jpg",
            startTime: "11月11日",
            allowNumber: "500",  //班级最大报名人数
            branchName: "长沙芙蓉区分院",
            enrollFee: "6000.00",  //报名费
            enrollCount: "3",   //班级当前报名人数
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
class enrollList extends baseApi{
    
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
        
        $field = 'cl_id as id,tse_id as enrollId,tse_state as enrollState,tse_status as enrollStatus, cl_name as className,cl_logo as logoUrl,cl_startTime as startTime,cl_allowableNumber as allowNumber,cl_enrolledCount as enrolledCount,br_name as branchName,tse_fee as enrollFee';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_status<>-1 and tse_state<>-1) as enrollCount from tang_student_enroll 
               LEFT JOIN tang_class on cl_id=tse_classId 
               LEFT JOIN tang_branch on br_id=tangCollege 
               LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id 
               where cl_state<>-1 and cl_status=1 and tse_status<>-1 and tse_userId="' . $this->uid .'" order by tse_createTime desc limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        
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
