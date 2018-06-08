<?php
/* 报名接口
 * @param $uname   type : string 用户id   must
 * @param $payType type : int    支付方式      默认 ：0
 * @param $classId type : int    班级id   must
 * @author adadsa
 * */
 
 /**
 * @api                    {post} /api/enroll.json 报名业务接口
 * @apiDescription         学员报名
 * @apiName                enroll.json
 * @apiGroup               学员
 * @apiPermission          adadsa 
 *
 * 
 * 
   @apiParam {string}     userId       用户id （userId,userName,mobile三选一）
   @apiParam {string}     userName     用户名     （userId,userName,mobile三选一）
   @apiParam {string}     mobile       手机号码 （userId,userName,mobile三选一）
   
   @apiParam {int}        classId      班级id
   @apiParam {int}        province     所属区域
   @apiParam {int}        payType      支付方式（可选，默认为0）
   @apiParam {string}     arrivalTime  接站时间（可选）
   @apiParam {string}     station      接站地点（可选）
   @apiParam {int}        counts       接站人数（可选）
   @apiParam {int}        isApp        是否是APP调用（可选：默认为1：APP调用   0： 雇员端调用）
   @apiParam {int}        spec         不受报名时间限制（可选，默认为0）
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
		code: 1001,
		msg: "报名成功", 
		data: null
	}
 *
 */
class enroll_json extends api{
	
    private $nowDate;
    private $db;
	private $sdk;
	private $userInfo;
	
    function run() {
		$this->db = new MySql();
		$this->nowDate = date('Y-m-d');	
		$this->sdk  = new openSdk();
		
        //验证参数是否存在		
        if(!isset($this->options['classId']) || empty($this->options['classId'])) return apis::apiCallback('1002','班级id为空');
//        if(!isset($this->options['province']) || empty($this->options['province'])) return apis::apiCallback('1002','所属区域为空');

		$userId = isset($this->options['userId']) ? trim($this->options['userId']) : '';
		$userName = isset($this->options['userName']) ? trim($this->options['userName']) : '';
		$mobile = isset($this->options['mobile']) ? trim($this->options['mobile']) : '';
		$isApp  = isset($this->options['isApp']) ? $this->options['isApp'] + 0 : 1;
		if($userId == '' && $userName == '' && $mobile == ''){
			return apis::apiCallback('1002','参数错误!');
		}
		
		$param = [
			'userId'	=> $userId,
			'userName'	=> $userName,
			'mobile'	=> $mobile,
		];
		
		$result = apis::request('/college/api/getUser.json', $param, true);

		if(isset($result['code'])){
			if($result['code'] == '1001'){
				$userInfo = $result['data'];
				$this->userInfo = $result['data'];
			}else{
				return apis::apiCallback('1002', $result['data']);
			}
		}else{
			return apis::apiCallback('1002','系统错误!');
		};

        $payType = (isset($this->options['payType']) && !empty($this->options['payType'])) ? $this->options['payType'] - 0 : 0;
        $spec = (isset($this->options['spec']) && !empty($this->options['spec'])) ? $this->options['spec'] - 0 : 0;
        $userId  = $userInfo['userId'];
        $classId = $this->options['classId'] - 0;
        $province = $this->options['province'];

        $enrollCondition = $this->db->getField('select cl_enrollCondition from tang_class where cl_id="' . $classId . '"');
        if($enrollCondition){
            $enrollCondition = unserialize($enrollCondition);
            $levelCondition = $enrollCondition['levelCondition'] + 0;
            if(in_array($levelCondition, array(2,3))){
                if($levelCondition == 2 && $this->userInfo['level'] + 0 < 3){
                    return apis::apiCallback(1005, '您不是创客以上会员');
                }else if($levelCondition == 3 && $this->userInfo['level'] + 0 != 4){
                    return apis::apiCallback(1005, '您不是创投会员');
                }
            }
            
            if($enrollCondition['isAuthed'] + 0 == 1 && substr($this->userInfo['auth'], 2, 1) != '1'){
                return apis::apiCallback(1006, '您没有身份证认证');
            }

            //未学习过:1 ，学习过：0
            if($enrollCondition['enrollEver'] + 0 == 1){
                //检查该学员有无往期学习记录，如有，则不允许报名
                $records = $this->db->getRow('select count(cs_id) as count,cl_name from tang_class_student LEFT JOIN tang_class on cs_classId=cl_id where cs_studentId="' . $userId . '" and cs_classId<>"' . $classId . '"');
                if($records['count']) return apis::apiCallback('1002','您已报过往期班级：' . $records['cl_name']);
            }

            //如果两次以上报名未报到，则列为黑名单
            if($enrollCondition['isBlack'] + 0 == 1){//1：非黑名单，0：黑名单
                $enrollClass = $this->db->getRow('select count(tse_id) as tse_count from tang_student_enroll left join tang_class on cl_id=tse_classId where tse_userId="' . $userId . '" and tse_classId<>"' . $classId . '" and tse_state=1 and tse_status in (0,1)');
                if($enrollClass['tse_count'] >= 2){
                    return apis::apiCallback(1002, '您已报过两期往期班级未报到，已被列入黑名单！');
                }
            }
        }else {
            if($this->userInfo['level'] < 3) return apis::apiCallback(1005, '您不是创客以上会员');
            if(substr($this->userInfo['auth'], 2, 1) != '1') return apis::apiCallback(1006, '您没有身份证认证');

            //检查该学员有无往期学习记录，如有，则不允许报名
            $records = $this->db->getRow('select count(cs_id) as count,cl_name from tang_class_student LEFT JOIN tang_class on cs_classId=cl_id where cs_studentId="' . $userId . '" and cs_classId<>"' . $classId . '"');
            if($records['count']) return apis::apiCallback('1002','您已报过往期班级：' . $records['cl_name']);

            //检查该学员是否有过通过审核，但却未报到成功的记录，如有，则不允许报名
            $enrollClass = $this->db->getRow('select count(tse_id) as tse_count,cl_name,(select count(cs_id) from tang_class_student where cs_classId=tse_classId and cs_studentId=tse_userId) as cs_count from tang_student_enroll left join tang_class on cl_id=tse_classId where tse_userId="' . $userId . '" and tse_classId<>"' . $classId . '" and tse_state=1 and tse_status=1');
            if($enrollClass['tse_count'] && $enrollClass['cs_count'] == 0){
                return apis::apiCallback(1002, '您已报过往期班级：' . $enrollClass['cl_name'] . '，但未报到！');
            }
        }

        //检查该班级是否存在
        $classInfo = $this->db->getRow('select cl_name,cl_description,cl_cost,tangCollege,cl_state as state,cl_enrollStartTime as enrollStartTime,cl_enrollEndTime as enrollEndTime,cl_startTime as startTime from tang_class where cl_id="' . $classId . '" and cl_status=1');//班级信息
        if(!$classInfo) return apis::apiCallback('1002','班级不存在!');
        if($classInfo['enrollStartTime'] > $this->nowDate && $spec ==0) return apis::apiCallback(1002, '未到报名时间');
//         if($classInfo['enrollEndTime'] < $this->nowDate && $spec ==0) return apis::apiCallback(1002, '报名已结束');
// 		if($classInfo['startTime'] < $this->nowDate) return apis::apiCallback(1002, '报名时间不能超过开课时间');
        if($classInfo['state'] == -1) return apis::apiCallback(1002, '该班级已取消');
        if($classInfo['state'] == 2) return apis::apiCallback(1002, '该班级已结束'); 
        
        if($isApp){
            if($classInfo['state'] == 1 && $spec ==0) return apis::apiCallback(1002, '该班级已开课');
        }
        
        try {
            
            $this->db->beginTRAN();
            $userId = $this->userInfo['userId'];
            
            //检查用户是否报了该班
            $count = $this->db->getField('select count(cs_id) from tang_class_student where cs_classId="' . $classId . '" and cs_studentId="' . $userId . '"');
            if($count){
                throw new Exception('您已在该班级报到', -1);
            }
            
            //查核用户是否已报名
            $count = $this->db->getField('select count(tse_id) from tang_student_enroll where tse_classId="' . $classId . '" and tse_userId="' . $userId . '" and tse_state in (0,1) and tse_status in (0,1,2)');
            if($count){
                throw new Exception('您已报名', -2);
            }
            
            $enrollId = F::getTimeMarkID();
            $time     = date('Y-m-d H:i:s');

            $params = array(
                'outTradeNo'    => $enrollId,
                'outCreateTime' => $time,
                'buyID'         => $this->userInfo['id'],
                'buyNick'       => $this->userInfo['nick'],
                'totalMoney'    => $classInfo['cl_cost'],
                'totalScore'    => 0,
                'subject'       => '班级报名',
                'body'          => '用户' . $this->userInfo['nick'] . '报了班级“' . $classInfo['cl_name'] . '”',
                'showUrl'       => 'https://www.dttx.com',//暂时填写
                'dealType'      => 2,
                'returnType'    => 2
            );

            //生成订单
            $path = '/order/tcRegister';
            $result = $this->sdk->request($params, $path);

            if(!is_array($result)){
                throw new Exception($result, -3);
            }
            
            if($result['id'] != 'SUCCESS' && $result['id'] != 'SUCCESS_EMPTY'){
                throw new Exception($result['msg'], -4);
            }
            
            $orderId = $result['info']['order_id'];//获取订单号
            
            if(!$orderId){
                throw new Exception('获取订单号失败', -5);
            }
            
            //插入数据
            $data = array(
                'tse_id'           => $enrollId,
                'tse_userId'       => $userId,
                'tse_orderId'      => $orderId,
                'tse_userTrueName' => $this->userInfo['name'],
                'tse_certNum'      => $this->userInfo['certNum'],
                'tse_classId'      => $classId,
                'tse_fee'          => $classInfo['cl_cost'],
                'tse_payFee'       => 0.00,
                'tse_status'       => 1,
                'tse_state'        => $isApp ? 0 : 1,
                'tse_createTime'   => $time,
                'tse_payTime'      => $time,
                'tse_arrivalTime'  => isset($this->options['arrivalTime']) ? trim($this->options['arrivalTime']) : '',
                'tse_station'      => isset($this->options['station']) ? trim($this->options['station']) : '',
                'tse_counts'       => isset($this->options['counts']) ? trim($this->options['counts']) : '',
                'tse_province'     => isset($this->options['province']) ? trim($this->options['province']) : '',
            );

            $res = $this->db->insert('tang_student_enroll', $data);
            
            if(!$res){
                throw new Exception('插入报名表失败', -6);
            }
            
            $member = array('tangCollege' => $classInfo['tangCollege']);
            
            $this->db->update('tang_ucenter_member', $member, 'id="' . $userId . '"');
            
            $this->db->commitTRAN();
            return apis::apiCallback(1001);
        } catch (Exception $e) {
            $this->db->rollBackTRAN();
            return apis::apiCallback(1002, $e->getMessage());
        }
        
    }
	
    
}
