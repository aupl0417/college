<?php

class tableList extends member {
	function __construct($options) {
        parent::__construct($options, [50102]);
        $this->db = new MySql();
    }

    function run() {
        $classId = $this->options['id'] + 0;
        !$classId && die($this->show(message::getJsonMsgStruct('1002', '参数非法')));

        $data = [
            'code' => 50102,
        ];

        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }
        $userId = $this->db->getField('select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '"');
        !$userId && die($this->show(message::getJsonMsgStruct('1002', '用户不存在')));

        $tableList = array();
        $field = 'cta_id as id,DATE_FORMAT(cta_startTime, "%Y-%m-%d") as date,cta_startTime as startTime,cta_endTime as endTime,co_name as courseName,tra_name as trainSite,username as teacher,CASE cl_state WHEN 0 THEN "报名中" WHEN 1 THEN "开课中" ELSE "已结束" END as state';
        $sql = 'select ' . $field . ' from tang_class_table
                left join tang_class on cta_classId=cl_id
                left join tang_course on co_id=cta_courseId
                left join tang_trainingsite on cta_trainingsiteId=tra_id
                left join tang_ucenter_member on cta_teacherId=id
                where cl_status=1 and cta_classId="' . $classId . '" and cl_state<>-1';
        $tableList = $this->db->getAll($sql);

        if($tableList){
            foreach($tableList as $key=>&$val){
                $val['periodTime'] = date('H:i', strtotime($val['startTime'])) . ' ~ ' . date('H:i', strtotime($val['endTime']));
            }
        }

//        dump($tableList);die;
        $this->setLoopData('tableList', $tableList);
        $this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
    }

}
