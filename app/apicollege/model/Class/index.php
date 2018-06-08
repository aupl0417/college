<?php
 /**
 * @api                    {post} /Class/index 班级列表
 * @apiDescription         获取班级列表
 * @apiName                index
 * @apiGroup               Class
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId     设备类型
   @apiParam {string}     deviceID  设备id
   @apiParam {string}     signValue 签名串
   @apiParam {string}     userId    用户id
   @apiParam {int}        page      分页(可选)
   @apiParam {int}        pageSize  分页大小(可选)
 *
 *
 * @apiSuccessExample      Success-Response: 
 *	{
		code: 1001,
		msg: "操作成功",
		data: [
			{
				id: "2",
				className: "201610基础课程",
				logoUrl: "https://image.dttx.com/v1/tfs/T1v.KTB5KT1RCvBVdK.jpg",
				startTime: "10月13日", //开课开始时间
				endTime: "2016-10-31", //开课结束时间
				enrollFee: "6000.00",   //报名费 
				allowNumber: "500",    //班级最大报名人数
				branchName: "长沙分院",
				isHot: "1",
				enrollCount: "6"   //班级当前报名人数
				enrollStatus: 1,    //用户对应该班级是否报名 0：未报名；1：已报名      根据报名订单的审核通过就为1，其它都为0
				enrollInfo: "4/500 报名",
				classState: 1,  //班级状态   0 报名中 1开课中 2已结束
			},
		]
	}
 *
 */
class index extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
        $this->nowDate = date('Y-m-d');
    }
	
    function run() {
        
        $page     = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 0;
        $pageSize = isset($this->data['pageSize']) && !empty($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 0;
        
        $limit = '';
        if($page && $pageSize){
            $limit = ' limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        }
        
        $field = 'cl_id as id, cl_name as className,cl_logo as logoUrl,cl_startTime as startTime,cl_endTime as endTime,cl_cost as enrollFee,cl_allowableNumber as allowNumber,cl_enrolledCount as enrolledCount,br_name as branchName,cl_isHot as isHot,cl_state as classState';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_status<>-1 and tse_state<>-1) as enrollCount from tang_class 
               LEFT JOIN tang_branch on br_id=tangCollege 
               LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id 
               where cl_state<>-1 and cl_status=1';
        
        if($this->isTest == 1){
            $sql .= ' and cl_isTest=1';
        }else {
            $sql .= ' and cl_isTest=0';
        }
        
        $sql .= ' order by cl_createTime desc' . $limit;
        
        $result = $this->db->getAll($sql);
        !$result && $this->apiReturn(501);
        
        foreach ($result as $key=>&$val) {
            $val['logoUrl'] = $val['logoUrl'] ? TFS_APIURL . '/' . $val['logoUrl'] : '';
            $val['startTime'] = date('m月d日', strtotime($val['startTime']));
            $sql = 'select tc_level as level from tang_teacher_comment where tc_classId="' . $val['id'] . '" and tc_classTableId=0';
            $level = $this->db->getAll($sql);
            $val['goodRate'] = '0.0%';
            if($level){
                $goodCount = 0;
                foreach($level as $v){
                    if($v['level'] == 5){
                        $goodCount ++;
                    }
                }
                $val['goodRate'] = round((floor($goodCount)) * 100 / count($level), 2) . '%';
            }
            
            //$val['enrollInfo'] = $val['enrollCount'] > $val['allowNumber'] ? '已满员' : $val['enrollCount'] . '/' .  $val['allowNumber'] . '报名';
            //$enrollNum = $val['enrollCount'] == ($val['allowNumber']-1) ? ($val['allowNumber']-1) : $val['enrollCount'];
            $val['enrollInfo'] =  ($val['enrollCount'] + $val['enrolledCount']) . '/' .  $val['allowNumber'] . '报名';
            unset($val['enrolledCount']);
            $val['isHot'] = $val['classState'] == 0 ? 1 : 0;
            
            $val['enrollStatus'] = 0;
            if($this->uid){
                $status = $this->db->getField('select tse_status from tang_student_enroll where tse_classId="' . $val['id'] . '" and tse_userId="' . $this->uid . '" and tse_state=1 and tse_status<>-1');
                $val['enrollStatus'] = !$status ? 0 : 1;
            }
        }
        
        $this->apiReturn(1001, '', $result);
    }
    
}
