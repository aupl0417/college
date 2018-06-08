<?php

class interactionDetail extends member {
    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '参数非法')));
        
        $interactionId = $this->options['id'] + 0; //课程id
        $interactionInfo = array();
        $field = 'tsi_id,tsi_title as title,tsi_classId as classId,tsi_courseId as courseId,tsi_userId as userId,tsi_teacherId as teacherId,tsi_content as content,tsi_createTime as createTime,username,trueName,co_name as courseName,cl_name as className';
        $sql   = 'select ' . $field . ' from `tang_teacher_student_interaction`
                 LEFT JOIN tang_ucenter_member on tsi_userId=id
                 LEFT JOIN tang_class on cl_id=tsi_classId
                 LEFT JOIN tang_course on co_id=tsi_courseId
                 where tsi_id="' . $interactionId . '"';
        $interactionInfo = $this->db->getRow($sql);
        
        if($interactionInfo){
            $interactionInfo['user'] = $interactionInfo['trueName'] ? $interactionInfo['trueName'] : $interactionInfo['usename'];
            $replyList = $this->db->getAll('select tsi_id,tsi_classId as classId,tsi_courseId as courseId,tsi_userId as userId,tsi_teacherId as teacherId,tsi_content as content,tsi_createTime as createTime from tang_teacher_student_interaction where tsi_pid="' . $interactionInfo['tsi_id'] . '"');        
            if($replyList){
                foreach($replyList as $key=>&$val){
                    $fieldName = $val['userId'] == 0 ? 'teacherId' : 'userId';
                    $userInfo = $this->db->getRow('select username,trueName from tang_ucenter_member where id="' . $val[$fieldName] . '"');
                    $val['user'] = $userInfo['trueName'] ? $userInfo['trueName'] : $userInfo['username'];
                }
            }
            $interactionInfo['replyCount'] = count($replyList);
        }
        
        $data = [
            'code'     => 50203,
        ];
        
        $data = array_merge($data, $interactionInfo);
        $this->setLoopData('replyList', $replyList);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
    
}
