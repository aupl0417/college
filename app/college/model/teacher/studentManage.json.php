<?php

class studentManage_json extends member {

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
        
        $studentList = array();
        $field = 'id as DT_RowId,id,trueName,cc_classId as classId,cc_courseId as courseId,cs_score as score,gr_name as gradeName';
        $sql   = 'select ' . $field . ' from `tang_class_course` LEFT JOIN tang_class_student on cc_classId=cs_classId left join tang_course on cc_courseId=co_id left join tang_grade on co_gradeID=gr_id LEFT JOIN tang_ucenter_member on cs_studentId=id where cc_classId="' . $classId . '" and cc_courseId="' . $courseId . '"';
        $studentList = $dataGrid->create($this->options, $sql);
        
        if($studentList['data']){
            foreach ($studentList['data'] as $key=>&$val){
                $attendInfo = $this->db->getAll('select att_classTableId,att_createTime,if(att_state=0, "签到","签退") as att_state from tang_attendance where att_classId="' . $val['classId'] . '" and att_courseId="' . $val['courseId'] . '" and att_userId="' . $val['id'] . '" order by att_createTime desc');
                $val['attendList'] = '';
                $attendListString = '';
                if($attendInfo){
                    foreach($attendInfo as $k=>$v){
                        $attendListString .= $v['att_state'] . ' ' . $v['att_createTime'] . '<br>';
                    }
                }
                $val['attendList'] = $attendListString;
            }
        }
        
        echo json_encode($studentList);
    }

}
