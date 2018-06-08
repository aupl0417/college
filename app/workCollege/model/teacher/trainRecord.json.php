<?php

class trainRecord_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50030101]);
    }
	
    function run() {
		$id = $this->options['id'];//教师id
		
		$options = $this->options;
		$dataGrid = new DataGrid();
		
		$where = ' WHERE 1 ';
		
		$where .= " AND cta_teacherId='{$id}'";
		
		
		$field = 'cta_id,cta_startTime,cta_endTime,cta_classId,tra_name as trainingSite,gr_name as courseGrade,co_name as courseName,te_courseReward as teacherReward';
		$sql = "select {$field}  from tang_class_table
		       LEFT JOIN tang_trainingsite on cta_trainingsiteId=tra_id 
		       LEFT JOIN tang_class on cl_id=cta_classId 
		       LEFT JOIN tang_course on co_id=cta_courseId 
		       LEFT JOIN tang_teacher on cta_teacherId=te_userId 
		       LEFT JOIN tang_teacher_level on te_level=tl_id 
		       LEFT JOIN tang_grade on gr_id=cl_gradeId 
		       LEFT JOIN tang_teacher_class_commision on tcc_classId=cta_classId " . $where;
		
		$result = $dataGrid->create($this->options,$sql);
		
		$db = new MySql();
		if(!$result['data']){
		    $emptyInfo = array(
		        'draw'            => 0,
		        'recordsTotal'    => 0,
		        'recordsFiltered' => 0,
		        'data'            => [],
		    );
		    
		    die(json_encode($emptyInfo));
		    
		}
		
		foreach ($result['data'] as $key=>&$val){
		    $val['DT_RowId']   = 'row_'.$val['cta_id'];
		    $val['studentCount'] = $db->getField('select count(cs_id) from tang_class_student where cs_classId="' . $val['cta_classId'] . '"');
		    $val['teachingTime'] = $val['cta_startTime'] . '--' . $val['cta_endTime'];
		}
		
		echo json_encode($result);
    }
}
