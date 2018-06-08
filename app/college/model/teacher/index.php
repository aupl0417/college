<?php

class index extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {

        $field = 'te_id as id,trueName as teacher,te_photo as photo,te_sex,te_description as description,avatar';
        $sql  = 'select ' . $field . ' from tang_teacher LEFT JOIN tang_ucenter_member on id=te_userId where te_userId not in (650,645,133) and te_isLeave=0';

        $dwhere = ' and te_level=4';
        $dreamTeacher = $this->db->getAll($sql . $dwhere);
        $dreamTeacher = $this->arrangeTeacherInfo($dreamTeacher);

        $gwhere = ' and te_level=3';
        $goldenTeacher = $this->db->getAll($sql . $gwhere);
        $goldenTeacher = $this->arrangeTeacherInfo($goldenTeacher, 'gold');

        $twhere = ' and te_level in (1,2)';
        $teacher = $this->db->getAll($sql . $twhere);
        $teacher = $this->arrangeTeacherInfo($teacher, 'teacher');

        $this->setHeadTag('title', '讲师团-唐人大学'.SEO_TITLE);
        $this->setLoopData('dreamTeacher', $dreamTeacher);
        $this->setLoopData('goldenTeacher', $goldenTeacher);
        $this->setLoopData('teacher', $teacher);
        $this->setReplaceData(['menu'=>$this->options['PATH_MODEL']]);
		$this->setTempAndData();
        $this->show();
    }

    function arrangeTeacherInfo($array = array(), $type = 'dream'){
        if(!$array){
            return $array;
        }

        foreach($array as $key=>&$val){
            if(!$val['photo']){
                if($val['te_sex'] == 1){
                    $val['avatar'] = $type == 'dream' ? 'https://image.dttx.com/v1/tfs/T1S2_TByKT1RCvBVdK.jpg' : 'https://image.dttx.com/v1/tfs/T1sPhTBCdT1RCvBVdK.jpg';
                }else {
                    $val['avatar'] = $type == 'dream' ? 'https://image.dttx.com/v1/tfs/T1q0KTB5WT1RCvBVdK.jpg' : 'https://image.dttx.com/v1/tfs/T1FIxTBCET1RCvBVdK.jpg';
                }
            }
            $val['photo'] = $val['photo'] ? TFS_APIURL . '/' . $val['photo'] : $val['avatar'];
        }

        return $array;
    }


}
