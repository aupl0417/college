<?php

class courseInfo extends guest{
private $db;
    
    function __construct($options) {
        header("Content-type:text/html;charset=utf-8");
        parent::__construct($options);
		$this->db = new MySql();
		$this->data = $options;
    }
	
    function run() {
        $id = $this->data['id'] + 0;
        $id == 0 && die($this->show(message::getJsonMsgStruct('1002',  '课时id不能为空')));
        $app = isset($this->data['app']) ? $this->data['app'] + 0 : 0;
        !in_array($app, array(0, 1)) && die($this->show(message::getJsonMsgStruct('1002',  'app不合法')));
        
        $classId = $this->db->getField('select cta_classId from tang_class_table where cta_id="' . $id . '"');
        $classInfo = $this->db->getRow('select cl_name,cl_allowableNumber from tang_class where cl_id="' . $classId . '"');
        $classInfo['count'] = $this->db->getField('select count(tse_id) from tang_student_enroll where tse_classId="' . $classId . '" and tse_state<>-1 and tse_status<>-1');
        $classInfo['app'] = $app;
        $classInfo['enrollInfo'] = $classInfo['count'] > $classInfo['cl_allowableNumber'] ? '已满员' : $classInfo['count'] . '/' . $classInfo['cl_allowableNumber'] .'报名';
        $classInfo['title'] = '课程详情-C+商业系统';
        
        $date = date('Y-m-d', time());
        //课时数据
        $fields = 'cta_id,cta_startTime,cta_endTime,co_name,co_description,username,tra_address,tra_name';
        $sql    = 'select ' . $fields . ' from tang_class_table 
                   LEFT JOIN tang_course on co_id=cta_courseId 
                   LEFT JOIN tang_ucenter_member on cta_teacherId=id 
                   LEFT JOIN tang_trainingsite on cta_trainingsiteId=tra_id 
                   where cta_classId=' . $classId . ' and cta_id="' . $id . '"';
        
        $classTableData = $this->db->getAll($sql);
        foreach ($classTableData as $key=>&$val) {
            $val['cta_startTime'] = date('Y年m月d日', strtotime($val['cta_startTime']));
            $val['cta_endTime'] = date('Y年m月d日', strtotime($val['cta_endTime']));
        }
        
        $this->setHeadTag('title', '课程详情-C+商业系统');
        $this->setReplaceData($classInfo);
        $this->setLoopData('courseList', $classTableData);
        $this->setTempAndData();
        $this->show();
    }
    
}
