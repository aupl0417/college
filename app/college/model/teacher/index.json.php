<?php

class index_json extends member {

    function __construct($options) {
        parent::__construct($options, [501]);
        $this->db = new MySql();
    }

    function run() {
        $option = $this->options;
        $type = $option['type'];
        unset($this->options['type']);
        if(!$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $userId = $_SESSION['userID'];
//        $userId = 'aaa263f7936899d2ba78ab57d7a06844';
        $uid = $this->db->getField('select id from tang_ucenter_member where userId="' . $userId . '"');

        $dataGrid  = new DataGrid();

        $opStr = '<a href="/%s/?id=%s" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        if($type == 'record'){
            $sql = "select cl_id as DT_RowId,cl_id as id,cl_startTime as startTime,cl_endTime as endTime from tang_class_student
		    left join tang_class on cl_id=cs_classId
		    where cs_studentId='" . $uid . "' and cl_state in (1,2) and cl_status=1 and cl_isTest=0";
            $result = $dataGrid->create($this->options, $sql);

            if($result['data']){
                foreach($result['data'] as $key=>&$val){
                    $val['op'] = sprintf($opStr,'class/detail', $val['DT_RowId'], 'fa-edit', '查看班级');
                    $courseCount = $this->db->getField('select count(cta_id) from tang_class_table where cta_classId=' . $val['id'] . ' and cta_endTime<"' . date('Y-m-d H:i:s') . '"');
                    $attendCount = $this->db->getField('select count(att_id) from tang_attendance where att_classId="' . $val['id'] . '" and att_userId="' . $uid . '"');
                    $field = 'co_name as courseName,cc_credit';
                    $sql = 'select ' . $field . ' from tang_class_course LEFT JOIN tang_course on cc_courseId=co_id where cc_classId="' . $val['id'] . '"';
                    $res = $this->db->getAll($sql);
                    $val['courseList'] = '';
                    $val['credit'] = 0;
                    if($res){
                        foreach($res as $k=>$v){
                            $val['courseList'] .= $v['courseName'] . "<br>";
                            $val['credit'] += $v['cc_credit'];
                        }
                    }
                    $val['courseList'] = trim($val['courseList'], "<br>");
                    $val['attendRate'] = $courseCount ? round($attendCount * 100 / $courseCount, 2) . '%' : '0%';
                    $val['trainTime']  = $val['startTime'] . '-' . $val['endTime'];
                }
            }
        }else if($type == 'certificate') {
            $sql = 'select tce_id as DT_RowId,tce_id as id,tce_name as certName,tce_createTime as createTime from tang_certificate where tce_userId="' . $uid . '"';
            $result = $dataGrid->create($this->options, $sql);
            if($result['data']){
                foreach($result['data'] as $key=>$val){
                    $val['op'] = sprintf($opStr,'class/detail', $val['DT_RowId'], 'fa-edit', '查看班级');
                }
            }
        }

        echo json_encode($result);
    }

}
