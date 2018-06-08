<?php
/* 学员报到接口
 * @param $userId  type : string 用户id   must
 * @param $classId type : int    班级id   must
 * @author aupl
 * */
/**
 * @api                    {post} /Enroll/checkin.json 学员报到-新版本
 * @apiDescription         学员报到(新版本请使用该接口)
 * @apiName                checkin.json
 * @apiGroup               Enroll
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     用户id
   @apiParam {string}     classId    班级id
   @apiParam {string}     eId        雇员id
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001, 
    data: {
                'cl_name': '高级课程',
                'arraivalTime': '10:00',
                'cl_startTime': '2016-11-02',
                'cl_endTime': '2016-11-09',
                'hostel': '大唐之家双人间',
                'catering': '不包吃',
                'trainRoom': '308会议室'
            },
  }
 *
 *@apiErrorExample        Error-Response:
 *{
    code: 1004,
    msg: "您还未报名该班级",
    data: {
        className: "test班级(4)",
        condition: [
            "1、创客以上会员",
            "2、已实名制会员",
            "3、最近五期未进行学习",
            "4、报名时间：11月12日-11月14日" 
        ]
        }
    }
 */
class checkin_json extends baseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowDate = date('Y-m-d');
    }
	
    function run() {
        //验证参数是否存在
        (!isset($this->data['userId'])  || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(502);
//         (!isset($this->data['eId']) || empty($this->data['eId'])) && $this->apiReturn(1002, '雇员ID不能为空');
        
        $classId = $this->data['classId'];
        $eId = isset($this->data['eId']) ? $this->data['eId'] : '';
        $eid = '';
        if(substr_count($classId, '-') > 0){
            $classIdArray = explode('-', $classId);
            $eid = $classIdArray[1];
            $classId = $classIdArray[0] + 0;
        }
        $eId = empty($eId) ? $eid : $eId;
        
        //检查该班级是否存在
        $classInfo = $this->db->getRow('select cl_name,cl_description,cl_cost,cl_enrollStartTime,cl_enrollEndTime from tang_class where cl_id="' . $classId . '" and cl_state IN (0, 1)');//班级信息
        !$classInfo && $this->apiReturn(501);
        
        $return = array(
            'className' => $classInfo['cl_name'],
            'condition' => array(
                '1、创客以上会员',
                '2、已实名制会员',
                '3、最近五期未进行学习',
                '4、报名时间：'. date('m月d日', strtotime($classInfo['cl_enrollStartTime'])) . '-' . date('m月d日', strtotime($classInfo['cl_enrollEndTime']))
            )
        );
        
        $this->userInfo['level'] < 3 && $this->apiReturn('1002', '您不是创客以上会员', $return);
        substr($this->userInfo['auth'], 2, 1) != '1' && $this->apiReturn('1002', '您没有身份证认证', $return);
        
        //查核用户是否已报名
        $enrollInfo = $this->db->getRow('select tse_id,tse_status,tse_state from tang_student_enroll where tse_classId="' . $classId . '" and tse_userId="' . $this->uid . '" and tse_state<>-1 and tse_status<>-1');
        
        $msg = '';
        $cId = $classId;
        if(!$enrollInfo){
            $cId = 0;
            $code = '1002';
            $msg  = '您还未报名该班级';
        }else {
            $enrollInfo['tse_status'] == 0  && $this->apiReturn('1004', '您还未付报名费', $return);
            $enrollInfo['tse_status'] == -1 && $this->apiReturn('1004', '您的订单已取消', $return);
            $enrollInfo['tse_state']  == -1 && $this->apiReturn('1004', '您的订单未通过审核', $return);
            $enrollInfo['tse_state']  == 0  && $this->apiReturn('1004', '您的订单还未被审核', $return);
            $enrollInfo['tse_status'] == 2  && $this->apiReturn('1004', '请不要重复报到', $return);
            
            $count = $this->db->getField('select count(cs_id) from tang_class_student where cs_classId="' . $classId . '" and cs_studentId="' . $this->uid . '"');
            $count && $this->apiReturn('1004', '您已成功报到', $return);
        }
        
        //用于雇员端获取
        $cache = new cache();
        $cacheId = 'checkInScan_' . $eId . '_' . $classId;
        
        if(!$info = $cache->get($cacheId)){
            $info = array(
                'trueName' => $this->userInfo['name'],
                'username' => $this->userInfo['nick'],
                'mobile'   => $this->userInfo['tel'],
                'certNum'  => $this->userInfo['certNum'],
                'level'    => $this->userInfo['level'] == 3 ? '创客会员' : ($this->userInfo['level'] == 4 ? '创投会员' : '非创客会员'),
                'type'     => $this->userInfo['type'] == 1 ? '企业会员' : ($this->userInfo['type'] == 0 ? '个人会员' : ''),
                'auth'     => substr($this->userInfo['auth'], 2, 1) == '1' ? '已实名制' : '未实名制',
                'authImage'=> $this->userInfo['authImage'] ? unserialize($this->userInfo['authImage'])['imgs_1'] : '',
                'classId'  => $cId
            );
            
            $cache->set($cacheId, $info, 300);
        }else {
            $this->apiReturn('1002', '您前面还有学员正报到中，请耐心等候！', '您前面还有学员正报到中，请耐心等候！');
        }
        
        $sql = "SELECT cl_name,cl_startTime,cl_endTime,att1.at_value hostel,att2.at_value catering,tra_name as trainRoom FROM tang_class cl
                LEFT JOIN tang_attrib att1 ON att1.at_key=cl.cl_hostel AND att1.at_type=2
                LEFT JOIN tang_attrib att2 ON att2.at_key=cl.cl_catering AND att2.at_type=1
                LEFT JOIN tang_trainingsite on tra_id=cl.cl_defaultTrainingsiteId
                WHERE cl_id={$classId}";
        
        $resInfo = $this->db->getRow($sql);
        $resInfo['arraivalTime'] = date('H:i');
        $this->apiReturn($code ? $code : '1001', $msg ? $msg : '操作成功', $resInfo);
        
    }
    
}
