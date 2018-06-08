<?php

class detail_json extends member {

    function __construct($options) {
        parent::__construct($options, [502]);
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
        $uid = $this->db->getField('select id from tang_ucenter_member where userId="' . $userId . '"');

        $dataGrid  = new DataGrid();

        $opStr = '<a href="/%s/?id=%s" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        
        $result = array(
            'draw'            => 0,
            'recordsTotal'    => 0,
            'recordsFiltered' => 0,
            'data'            => [],
        );
        
        $fields = 'cta_id as DT_RowId,cta_id as id,cta_startTime as startTime,cta_endTime as endTime,co_name as courseName,cta_classId,tra_name as trainSite';
        $sql = "select {$fields} from tang_class_table
	    left join tang_course on co_id=cta_courseId
	    left join tang_trainingsite on tra_id=cta_trainingsiteId
	    where cta_teacherId='" . $uid . "' and cta_endTime<='" . date('Y-m-d H:i:s') . "'";
        
        $result = $dataGrid->create($this->options, $sql);
        
        
        if($result['data']){
            foreach($result['data'] as $key=>&$val){
                $val['op'] = sprintf($opStr,'class/detail', $val['DT_RowId'], 'fa-edit', '查看班级');
                $val['trainTime']   = $val['startTime'] . '-' . $val['endTime'];
                $val['teachHour'] = round((strtotime($val['endTime']) - strtotime($val['startTime'])) / 60, 2) . '分钟';
                $studentCount = $this->db->getField('select count(cs_id) from tang_class_student where cs_classId="' . $val['cta_classId'] . '"');
                $val['studentCount'] = $studentCount ? $studentCount : 0;
            }
        }
        
        echo json_encode($result);
    }

}
