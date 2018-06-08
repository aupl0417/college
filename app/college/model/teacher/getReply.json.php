<?php

class getReply_json extends member {

    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        (!isset($this->options['pid']) || empty($this->options['pid'])) && die($this->show(message::getJsonMsgStruct('1002', '参数非法')));
        
        $pid = $this->options['pid'] + 0;
//         $replyInfo = $this->db->getRow('select tsi_id,tsi_classId as classId,tsi_courseId as courseId,tsi_userId as userId where tsi_id="' . $pid . '"');
        
        $replyList = array();
        $replyList = $this->db->getAll('select tsi_id,tsi_classId as classId,tsi_courseId as courseId,tsi_userId as userId,tsi_teacherId as teacherId,tsi_content as content,tsi_createTime as createTime from tang_teacher_student_interaction where tsi_pid="' . $pid . '"'); 
        
        if($replyList){
            foreach($replyList as $key=>&$val){
                if($val['userId'] == 0){
                    $fieldName = 'teacherId';
                }else {
                    $fieldName = 'userId';
                }
                $user = $this->db->getRow('select username,trueName from tang_ucenter_member where id="' . $val[$fieldName] .'"');
                $val['user'] = $user['trueName'] ? $user['trueName'] : $user['username'];
            }
        }
        
        $this->show(message::getJsonMsgStruct('1001', $replyList));
    }

}
