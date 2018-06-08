<?php

class reply_json extends member {

    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        (!isset($this->options['pid']) || empty($this->options['pid'])) && die($this->show(message::getJsonMsgStruct('1002', '参数非法')));
        (!isset($this->options['content']) || empty($this->options['content'])) && die($this->show(message::getJsonMsgStruct('1002', '请输入评论内容！')));
        (!isset($this->options['courseId']) || empty($this->options['courseId'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        (!isset($this->options['classId']) || empty($this->options['classId'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        
        $pid = $this->options['pid'] + 0;
//         $replyInfo = $this->db->getRow('select tsi_id,tsi_classId as classId,tsi_courseId as courseId,tsi_userId as userId where tsi_id="' . $pid . '"');
        $teacherId = $this->db->getField('select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '"');
        $data = array(
            'tsi_pid' => $this->options['pid'] + 0,
            'tsi_userId' => 0,
            'tsi_classId' => $this->options['classId'] + 0,
            'tsi_courseId' => $this->options['courseId'] + 0,
            'tsi_content'  => $this->options['content'],
            'tsi_createTime' => date('Y-m-d H:i:s'),
            'tsi_teacherId'  => $teacherId
        );
        
        $res = $this->db->insert('tang_teacher_student_interaction', $data);
        !$res && die($this->show(message::getJsonMsgStruct('1002', '回复失败')));
        
        $this->show(message::getJsonMsgStruct('1001', '回复成功'));
    }

}
