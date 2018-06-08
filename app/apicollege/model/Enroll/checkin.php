<?php
/* 学员报到接口
 * @param $userId  type : string 用户id   must
 * @param $classId type : int    班级id   must
 * @author aupl
 * */
/**
 * @api                    {post} /Enroll/checkin 学员报到-已废弃
 * @apiDescription         学员报到(已废弃,新版本请勿使用)
 * @apiName                checkin
 * @apiGroup               Enroll
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     用户id
   @apiParam {string}     classId    班级id
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "报到成功",
    data: null
  }
 *
 */
class checkin extends baseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowDate = date('Y-m-d');
    }
	
    function run() {
        //验证参数是否存在
        (!isset($this->data['userId'])  || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(502);
        
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
        $classInfo = $this->db->getRow('select cl_name,cl_description,cl_cost from tang_class where cl_id="' . $classId . '" and cl_state IN (0, 1)');//班级信息
        !$classInfo && $this->apiReturn(501);
        
        //查核用户是否已报名
        $enrollInfo = $this->db->getRow('select tse_id,tse_status,tse_state from tang_student_enroll where tse_classId="' . $classId . '" and tse_userId="' . $this->uid . '" and tse_state<>-1 and tse_status<>-1');
        
        $msg = '';
        $cId = $classId;
        if(!$enrollInfo){
            $cId = 0;
            $msg = '您还未报名该班级';
            $code = 1002;
        }else {
            $enrollInfo['tse_status'] == 0  && $this->apiReturn(509);
            $enrollInfo['tse_status'] == -1 && $this->apiReturn(510);
            $enrollInfo['tse_state']  == -1 && $this->apiReturn(511);
            $enrollInfo['tse_state']  == 0  && $this->apiReturn(512);
            $enrollInfo['tse_status'] == 2  && $this->apiReturn(513);
            $enrollInfo['tse_status'] == 3  && $this->apiReturn(518);
            
            $count = $this->db->getField('select count(cs_id) from tang_class_student where cs_classId="' . $classId . '" and cs_studentId="' . $this->uid . '"');
            $count && $this->apiReturn('1004', '您已成功报到');
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
				'classId'  => $cId,
            );
            
            $cache->set($cacheId, $info, 300);
        }else {
            $this->apiReturn(1002, '您前面还有学员正报到中，请耐心等候！');
        }
        
        $this->apiReturn($code ? $code : 1001, $msg ? $msg : '扫码成功');
        
    }
    
}
