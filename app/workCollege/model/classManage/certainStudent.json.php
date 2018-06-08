<?php
/*=============================================================================
#     FileName: certainStudent.json.php
#         Desc: 确认学员已经报到
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-14 14:27:32
#      History:
#      Paramer:
=============================================================================*/
class certainStudent_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        $options = $this->options;

        if (isset($options['enroll']) && $options['enroll'] == 1) {
            $notEnroll = true;
        }else{
            if (!isset($options['id']) || empty($options['id'])) {
                die($this->show(message::getJsonMsgStruct(1002,'报名ID参数错误')));
            }
        }

        if (!isset($options['clID']) || empty($options['clID'])) {
            die($this->show(message::getJsonMsgStruct(1002,'班级ID参数错误')));
        }

        $db = new MySql();
        //先报名
        if ($notEnroll) {
            if (!isset($options['mobile']) || empty($options['mobile'])) {
                die($this->show(message::getJsonMsgStruct(1002,'手机号码参数错误')));
            }

            $enrollRes = apis::request('college/api/enroll.json',array('classId'=>$options['clID'],'mobile'=>trim($options['mobile']),'isApp'=>0),true); 

            if (1001 != $enrollRes['code']) {
                die($this->show(message::getJsonMsgStruct(1002,$enrollRes['data'])));
            }

            $userId = $db->getField("SELECT id FROM tang_ucenter_member WHERE mobile='{$options['mobile']}'");

            //只允许报名一个班级
            $isEnrolled = $db->count('tang_student_enroll',"tse_userId='{$userId}' AND tse_state IN(1,2) AND tse_classId<>'{$options['clID']}' AND tse_status<>-1");

            if ($isEnrolled) {
                die($this->show(message::getJsonMsgStruct('1002', '已经报名其它班级,或已经参加过往期培训')));
            }

            //现场报名报到,需要过滤条件
            $qualification = $this->checkQualification($userId);

            if (!$qualification['state']) {
                die($this->show(message::getJsonMsgStruct(1002,array('black'=>true,'log'=>$qualification['log']))));
            }

            //将报名状态改成审核通过
            $tseId = $db->getField("SELECT tse_id FROM tang_student_enroll WHERE tse_classId='{$options['clID']}' AND tse_userId='{$userId}' AND tse_state=0");

            if (empty($tseId)) {
                die($this->show(message::getJsonMsgStruct(1002,'报名订单添加失败，请联系管理员，并稍后处理该学员报到')));
            }

            $options['id'] = $tseId;
            $db->update('tang_student_enroll',['tse_state'=>1],"tse_id='{$tseId}'");

        }else{
            $userId = $db->getField("SELECT tse_userId FROM tang_student_enroll WHERE tse_id='{$options['id']}'");
        }

        if ($db->count('tang_class_student',"cs_classId='{$options['clID']}' AND cs_studentId='{$userId}'")) {
            die($this->show(message::getJsonMsgStruct(1002,'该学员已经报到，无需重复，请点击【延迟处理】')));
        }

        try{
            $db->beginTRAN();
            $now = F::mytime();
            $data = array(
                'tse_status'      => 2,
                'tse_checkInTime' => $now,
                'tse_team'        => $options['team'],
            );


            $res = $db->update('tang_student_enroll',$data," tse_id='{$options['id']}'");

            if (false == $res) {
                throw new Exception('修改报名表错误,可请联系管理员,并稍后处理该学员报到',-1);
            }

            $info = array(
                'cs_classId'    => $options['clID'],
                'cs_studentId'  => $userId,
                'cs_createTime' => $now,
            );

            $insertClassres = $db->insert('tang_class_student', $info);

            if(1 != $insertClassres){
                throw new Exception('插入班级失败，可请联系管理员，并稍后处理该学员报到', -2);
            }

            //$userInfo = array_filter($db->getRow("SELECT trueName,certNum FROM tang_ucenter_member WHERE id='{$userId}'"));
            $userInfo = $db->getRow("SELECT trueName,certNum FROM tang_ucenter_member WHERE id='{$userId}'");

            $updData = array();
            if ($options['trueName'] != $userInfo['trueName']) {
                $updData['trueName'] = $options['trueName'];
            }

            if ($options['certNum'] != $userInfo['certNum']) {
                $updData['certNum'] = $options['certNum'];
            }

            //是否需要补充会员信息
            if (count($updData) > 0) {
                $updUserInfoRes = $db->update('tang_ucenter_member',$updData," id='{$userId}'");
                if (false == $updUserInfoRes) {
                    throw new Exception('完善会员信息失败,请重试。', -3);
                }
            }

            $cache = new cache();
            $key = 'checkInScan_'.$_SESSION['userID']."_{$options['clID']}";
            if ($cache->get($key)) {
                $cache->del($key);
            }

            //if ($options['cached'] == 1) {
            //    $cache = new cache();
            //    $key = 'checkInScan_'.$_SESSION['userID']."_{$options['clID']}";
            //    $cache->del($key);
            //}

            $db->commitTRAN();
            die($this->show(message::getJsonMsgStruct(1001,'确认成功')));
        }catch(Exception $e){
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct(1002,$e->getMessage())));
        }
    }

    //检查不良记录
    private function checkQualification($userId){
        $db = new MySql();
        //报名未报到
        $sql = "SELECT tse_createTime,cl_name,'报名后未报到' result FROM tang_student_enroll tse LEFT JOIN tang_class cl ON tse.tse_classId=cl.cl_id
            WHERE tse.tse_state=1 AND tse.tse_status=1 AND cl.cl_state=2 AND tse.tse_userId='{$userId}'";
        $enrollNotArrival = $db->getAll($sql);

        //学习记录
        $sql = "SELECT cs_createTime,cl_name,'已经学习' result FROM tang_class_student cs  LEFT JOIN tang_class cl ON cs.cs_classId=cl.cl_id
            WHERE cl.cl_state=2 AND cs.cs_studentId='{$userId}'";
        $studyLog = $db->getAll($sql);

        if (count($enrollNotArrival) > 1 || !empty($studyLog)) {
            $res['state'] = false;
            $res['log'] = array(
                'enrollNotArrival' => $enrollNotArrival,
                'studyLog'         => $studyLog,
            );

            return $res;
        }

        return ['state'=>true];
    }
}
