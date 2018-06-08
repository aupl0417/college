<?php

class interactionManage_json extends member {

    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '课程ID不能为空')));
        (!isset($this->options['cId']) || empty($this->options['cId'])) && die($this->show(message::getJsonMsgStruct('1002', '班级ID不能为空')));
        
        $dataGrid  = new DataGrid();
        
        $classId = $this->options['cId'] + 0;//班级id
        $courseId = $this->options['id'] + 0; //课程id
        
        $interactionList = array();
        $field = 'tsi_id as DT_RowId,id,tsi_title as title,tsi_classId as classId,tsi_courseId as courseId,tsi_userId as userId,tsi_teacherId as teacherId,tsi_content as content,tsi_createTime as createTime,username,trueName,co_name as courseName,cl_name as className';
        $sql   = 'select ' . $field . ' from `tang_teacher_student_interaction` 
                 LEFT JOIN tang_ucenter_member on tsi_userId=id 
                 LEFT JOIN tang_class on cl_id=tsi_classId 
                 LEFT JOIN tang_course on co_id=tsi_courseId 
                 where tsi_classId="' . $classId . '" and tsi_courseId="' . $courseId . '" and tsi_teacherId=(select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '") and tsi_pid=0';
        $interactionList = $dataGrid->create($this->options, $sql);
        $opStr = '<a href="/teacher/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        if($interactionList['data']){
            foreach ($interactionList['data'] as $key=>&$val){
                $replyCount = $this->db->getField('select count(tsi_id) from tang_teacher_student_interaction where tsi_pid="' . $val['DT_RowId'] . '"');
                $val['replyCount'] = $replyCount ? $replyCount : 0;
                $val['op'] = sprintf($opStr, 'interactionDetail', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '详情');
            }
        }
        
        echo json_encode($interactionList);
    }

}
