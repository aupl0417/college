<?php
/**
 * @api                    {post} /Class/detail 班级详情
 * @apiDescription         获取班级详情
 * @apiName                detail
 * @apiGroup               Class
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId     设备类型
   @apiParam {string}     deviceID  设备id
   @apiParam {string}     signValue 签名串
 * @apiParam {int}   	  id        班级id
   @apiParam {string}     userId    学员id 
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        id: "125",
        className: "test班级（6）",
        logoUrl: "https://image.dttx.com/v1/tfs/T1zaETBCKT1RCvBVdK.png",
        startTime: "2016-11-12",   //报名开始时间
        endTime: "2016-11-12",     //报名结束时间
        classStartTime: "2016-11-12",   //班级开始时间
        classEndTime: "2016-11-12",     //班级结束时间
        catering: "不包吃",
        hostel: "不包住",
        conditions: "所有学员",    //学习前置条件
        enrollFee: "10.00",
        allowNumber: "10",
        state: "0",
        status: "1",
        branchName: "邵阳分院",
        descriptions: "1231",
        enrollCount: "2",
        enrollInfo: "2/10报名",
        enrollStatus: "1",   //报名状态：-2:未报名;     登录状态：0：未付款；1：已付款；2：已报到；3：已转人；-1：订单已取消
        enrollState: "-1",   //审核状态：-2:未报名时;   登录状态：0：初始；1：审核通过；-1：已拒绝
        isCollect: 0,        //0:未收藏；1：已收藏
        enrollId: "2016111414240053513415834", //报名订单ID
        courseList: [  //课程列表
            {
                cid: "214",
                courseName: "test课程（2）",
                descriptions: [
                    "test课程（2）",
                    "课程描述"
                ],
                startTime: "11月12日 14:00",
                endTime: "11月12日 15:00",
                teacherName: null,
                trainAddress: "测试",
                desc: "test课程（2） 课程描述"
            }
        ],
        glimpse: [ //学员风采
            {
                id: "52",
                imageUrl: "http://192.168.3.201:80/v1/tfs/T1zaETBCWT1RCvBVdK.png",//图片地址
                title: "ccccccccccc" //标题
            }
         ]
       }
    }
 *
 * @apiErrorExample        Error-Response:
 *{
 * "id": "SUCCESS_EMPTY",
 * "msg": "没有获取到数据",
 * "info": null
 * }
 *
 */
class detail extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowDate = date('Y-m-d');
    }
	
    function run() {
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(502);
        
        $id = $this->data['id'] + 0;//班级id
        $field = 'cl_id as id, cl_name as className,cl_logo as logoUrl,cl_enrollStartTime as startTime,cl_enrollEndTime as endTime,cl_startTime as classStartTime,cl_endTime as classEndTime,cl_catering as catering,cl_hostel as hostel,cl_condition as conditions,cl_cost as enrollFee,cl_allowableNumber as allowNumber,cl_enrolledCount as enrolledCount,cl_state as state,cl_status as status,br_name as branchName,cl_description as descriptions';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_state<>-1 and tse_status<>-1) as enrollCount from tang_class 
               LEFT JOIN tang_branch on br_id=tangCollege 
               LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id 
               LEFT JOIN tang_area on a_code=tra_areaId 
               where cl_state<>-1 and cl_status=1 and cl_id="' . $id . '"';
        
        $result = $this->db->getRow($sql);
        !$result && $this->apiReturn(1002, '暂无班级信息');
        
        
        $sql = "SELECT at_value FROM tang_attrib WHERE at_key='%d' AND at_type='%d'";
        
        $result['hostel']   = $this->db->getField(sprintf($sql,$result['hostel'],2));
        $result['catering'] = $this->db->getField(sprintf($sql,$result['catering'],1));
        
        $studyDirection = $this->db->getAll("SELECT stc_name FROM tang_study_condition WHERE stc_id IN({$result['conditions']})");
        $result['conditions'] = join(' | ',array_column($studyDirection,'stc_name'));
        $result['enrollInfo']  = ($result['enrollCount'] + $result['enrolledCount']) . '/' . $result['allowNumber'] . '报名';
        $logo = $this->db->getField('select tcp_filename as logo from tang_class_picture where tcp_classId="' . $id . '" and tcp_isLogo=1');
        $result['logoUrl'] = $result['logoUrl'] ? TFS_APIURL . '/' . $result['logoUrl'] : ($logo ? TFS_APIURL . '/' . $logo : '');
        $result['enrollStatus'] = -2;
        $result['enrollState']  = -2;
        $result['isCollect']    =  0;
        $result['descriptions']    =  F::TextToHtml($result['descriptions']);
        $result['enrollId'] = '';
        unset($result['enrolledCount']);
        
        if($this->uid){
            $enrollInfo = $this->db->getRow('select tse_id,tse_status,tse_state from tang_student_enroll where tse_userId="' . $this->uid .'" and tse_classId="' . $id .'" and tse_state<>-1 and tse_status<>-1 order by tse_id desc limit 1');
            if($enrollInfo){
                $result['enrollId']     = $enrollInfo['tse_id'];
                $result['enrollStatus'] = $enrollInfo['tse_status'];
                $result['enrollState']  = $enrollInfo['tse_state'];
            }
            $count = $this->db->getField('select count(tcc_id) from tang_class_collection where tcc_userId="' . $this->uid . '" and tcc_classId="' . $id . '" and tcc_deviceId="' . $this->data['deviceID'] . '"');
            if($count){
                $result['isCollect'] = 1;
            }
        }
        
        $courseIds = $this->db->getAll('select cc_courseId from tang_class_course where cc_classId="' . $id . '"');
        $courseIds = array_column($courseIds, 'cc_courseId');
        
        $courseList = array();
        if($courseIds){
            $sql = 'select cta_id as cid,co_name as courseName, co_description as descriptions,cta_startTime as startTime,cta_endTime as endTime,username as teacherName,tra_address as trainAddress from tang_class_table
               LEFT JOIN tang_ucenter_member on cta_teacherId=id
               LEFT JOIN tang_course on co_id=cta_courseId
               LEFT JOIN tang_trainingsite on tra_id=cta_trainingsiteId
               where cta_courseId in (' . implode(',', $courseIds) . ') and cta_classId="' . $id . '" order by cta_startTime asc';
            
            $courseList = $this->db->getAll($sql);
            
            foreach ($courseList as $key=>&$val) {
                $val['startTime'] = date('m月d日 H:i', strtotime($val['startTime']));
                $val['endTime'] = date('m月d日 H:i', strtotime($val['endTime']));
                $val['descriptions'] = trim($val['descriptions']);
                $val['desc'] = $val['descriptions'];
                $val['descriptions'] = explode("\n", $val['descriptions']);
            }
        }
        
        $glimpse = $this->db->getAll('select tcp_id as id,tcp_filename as imageUrl,tcp_title as title from tang_class_picture where tcp_classId="' . $id . '" order by tcp_isLogo desc, tcp_sort asc');
        
        if($glimpse){
            foreach ($glimpse as &$val){
                $val['imageUrl'] = TFS_APIURL . '/' . $val['imageUrl'];
            }
        }else {
            $glimpse[0]['imageUrl'] = $result['logoUrl'];
        }
        
        $result['courseList'] = $courseList;
        $result['glimpse']    = $glimpse ? $glimpse : array();
        $this->apiReturn(1001, '', $result);
    }
    
}
