<?php

class review_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500105]);
    }

    function run() {
        $options = $this->options;
        (!isset($options['id']) || empty($options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '参数错误')));
        (!isset($options['state']) || empty($options['state'])) && die($this->show(message::getJsonMsgStruct('1002', '请选择审核落地')));
        
        $db = new MySql();
        
        $enrollInfo = $db->getRow('select tse_state,tse_status,tse_classId,tse_userId from tang_student_enroll where tse_id="' . $options['id'] . '"');
        !$enrollInfo && die($this->show(message::getJsonMsgStruct('1002', '该数据不存在')));
        $enrollInfo['tse_status'] == 0 && die($this->show(message::getJsonMsgStruct('1002', '该订单未付款')));
        $enrollInfo['tse_status'] == 2 && die($this->show(message::getJsonMsgStruct('1002', '该订单已报到')));
        $enrollInfo['tse_status'] == -1 && die($this->show(message::getJsonMsgStruct('1002', '该订单已关闭')));
        $enrollInfo['tse_state'] == -1 && die($this->show(message::getJsonMsgStruct('1002', '该订单审核没通过')));
        
        //$enrollMaxCount = $db->getField('select cl_allowableNumber from tang_class where cl_id="' . $enrollInfo['tse_classId'] . '" and cl_status=1 and cl_state=0');
        $enrollMaxCount = $db->getField('select cl_allowableNumber from tang_class where cl_id="' . $enrollInfo['tse_classId'] . '" and cl_status=1');
        !$enrollMaxCount && die($this->show(message::getJsonMsgStruct('1002', '班级不在正常状态')));
        
//         $enrollCount = $db->getField('select count(tse_id) from tang_student_enroll where tse_classId="' . $enrollInfo['tse_classId'] . '" and tse_status<>-1 and tse_state<>-1');
//         ($enrollCount > $enrollMaxCount) && die('<span class="font-red">该班级报名人数已满</span>');

        $data = array(
            'tse_state'  => $options['state'],
            'tse_reason' => $options['reason'],
            'tse_eId'    => $_SESSION['userID'],
            'tse_eTime'  => date('Y-m-d H:i:s')
        );

        if ($options['state'] == 1) {
            $userInfo = $db->getRow('select level,auth from tang_ucenter_member where id="' . $enrollInfo['tse_userId'] . '"');
            
            $enrollCondition = $db->getField('select cl_enrollCondition from tang_class where cl_id="' . $enrollInfo['tse_classId'] . '" and cl_state in (0,1) and cl_status=1');
            $enrollCondition = unserialize($enrollCondition);
            $levelCondition = $enrollCondition['levelCondition'] + 0;
            if(in_array($levelCondition, array(2,3))){
                if($levelCondition == 2 && $userInfo['level'] + 0 < 3){
                    die($this->show(message::getJsonMsgStruct('1005', '您不是创客以上会员')));
                }else if($levelCondition == 3 && $userInfo['level'] + 0 != 4){
                    die($this->show(message::getJsonMsgStruct('1005', '您不是创投会员')));
                }
            }
            
            if($enrollCondition['isAuthed'] + 0 == 1 && substr($userInfo['auth'], 2, 1) != '1'){
                die($this->show(message::getJsonMsgStruct('1006', '您没有身份证认证')));
            }
            
            //未学习过:1 ，学习过：0
            if($enrollCondition['enrollEver'] + 0 == 1){
                //检查该学员有无往期学习记录，如有，则不允许报名
                $records = $db->getRow('select count(cs_id) as count,cl_name from tang_class_student LEFT JOIN tang_class on cs_classId=cl_id where cs_studentId="' . $enrollInfo['tse_userId'] . '" and cs_classId<>"' . $enrollInfo['tse_classId'] . '"');
                $records['count'] && die($this->show(message::getJsonMsgStruct('1002', '您已报过往期班级：' . $records['cl_name'])));
            }
            
            //如果两次以上报名未报到，则列为黑名单
            if($enrollCondition['isBlack'] + 0 == 1){//1：非黑名单，0：黑名单
                $enrollClass = $db->getRow('select count(tse_id) as tse_count from tang_student_enroll left join tang_class on cl_id=tse_classId where tse_userId="' . $enrollInfo['tse_userId'] . '" and tse_classId<>"' . $enrollInfo['tse_classId'] . '" and tse_state=1 and tse_status in (0,1)');
                if($enrollClass['tse_count'] >= 2){
                    die($this->show(message::getJsonMsgStruct('1002', '您已报过两期往期班级未报到，已被列入黑名单！')));
                }
            }
            
            //只允许报名一个班级
//             $isEnrolled = $db->count('tang_student_enroll',"tse_userId='{$enrollInfo['tse_userId']}'
//                 AND tse_state IN(1,2) AND tse_status<>-1 AND tse_classId<>'{$enrollInfo['tse_classId']}'");
//             if ($isEnrolled) {
//                 die($this->show(message::getJsonMsgStruct('1002', '已经参加过往期培训，或已经报名其它班级')));
//             }

            $teamInfo = apis::request('/college/api/arrangeTeam.json',array('classId'=>$enrollInfo['tse_classId']),true);
            $data['tse_team'] = $teamInfo['data']['team'];
        }

        $noticeParam = array(
            'state'  => $options['state'],
            'tseID'  => $options['id'],
        );

        apis::request('/college/api/enrollReviewNotice.json',$noticeParam,true);

        $res = $db->update('tang_student_enroll', $data, 'tse_id="' . $options['id'] . '"');
        $res === false && die($this->show(message::getJsonMsgStruct('1002', '操作失败')));

        apis::request('/college/api/sendEnrollCheckSms.json',array('id'=>$options['id']),true);

        die($this->show(message::getJsonMsgStruct('1001', '操作成功')));
    }
}
