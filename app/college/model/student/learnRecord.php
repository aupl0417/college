<?php

class learnRecord extends member {
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

        $learnList = array();
        $field = 'cta_id as id,co_name as courseName,username as teacher';
        $sql = 'select ' . $field . ' from tang_class_table
                left join tang_class on cta_classId=cl_id
                left join tang_course on co_id=cta_courseId
                left join tang_ucenter_member on cta_teacherId=id
                where cl_status=1 and cta_classId="' . $classId . '" and cl_state<>-1';
        $learnList = $this->db->getAll($sql);

        if($learnList){
            foreach($learnList as $key=>&$val){
                $attendList = $this->db->getAll('select att_createTime,att_state from tang_attendance where att_classTableId="' . $val['id'] . '" and att_userId="' . $userId . '"');
                $val['attendTime'] = '';
                if($attendList){
                    foreach($attendList as $k=>$v){
                        if($v['att_state'] == 0){
                            $val['attendTime'] .= date('H:i', strtotime($v['att_createTime']));
                        }else {
                            $val['attendTime'] .= '<br>' . date('H:i', strtotime($v['att_createTime']));
                        }
                    }
                }
            }
        }

        $this->setLoopData('learnList', $learnList);
        $this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
    }

}
