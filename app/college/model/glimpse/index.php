<?php

class index extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {
        $this->setHeadTag('title', '学员风采-相册列表页'.SEO_TITLE);
		// $this->setHeadTag('keywords', ''.SEO_KEYWORDS);
		// $this->setHeadTag('description', SEO_DESCRIPTION.'');

        //学员风采
        $field = 'cl_id as id,cl_logo as filename,cl_name as className';
        $where = 'where cl_id not in (135,136,138,170) and cl_state<>-1 and cl_status=1 and cl_isTest=0';
        $order = 'order by cl_id desc';
        $page = new page('tang_class', $field, $this->options['page'] + 0, 12, 0, $where, $order);
        $page->getDivPage();
        $glimpseList = $page->getPage();
        foreach($glimpseList as &$val){
            $val['className'] = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $val['className']);
            $val['filename'] = TFS_APIURL . '/' . $val['filename'];
        }
        
        $this->setReplaceData(['menu' => $this->options['PATH_MODEL'], 'pagelist' => $page->getMenu()]);
        $this->setLoopData('glimpseList', $glimpseList);
		$this->setTempAndData();
        $this->show();
    }

    private function arrangeClassInfo($class){
        if(!$class){
            return $class;
        }

        $classIds = array_column($class, 'id');
        $classIds = implode(',', $classIds);

        $esql = "SELECT tse_classId,count(1) total FROM tang_student_enroll WHERE tse_classId IN($classIds) AND tse_state !=-1 AND tse_status !=-1 GROUP BY tse_classId";
        $enrollNum = $this->db->getAll($esql);

        if (!empty($enrollNum)) {
            $enrollNum = array_column($enrollNum,'total','tse_classId');
        }

        foreach ($class as &$v) {
            $v['logoUrl']   = TFS_APIURL.'/'.$v['logoUrl'];
            $v['enrollNum'] = intval($enrollNum[$v['id']]);
            $course = $this->db->getAll('select cc_classId,cc_courseId,co_name as courseName from tang_class_course LEFT JOIN tang_course on co_id=cc_courseId where cc_classId=' . $v['id'] . ' and co_state=1 limit 2');
            $v['content']   = '';
            if($course){
                $v['content']  = '第1课：' . $course[0]['courseName'];
                $v['content'] .= $course[1] ? "<br/>" . '第2课：' . $course[1]['courseName'] : '';
            }
        }

        return $class;
    }
	
    function arrangeTeacherInfo($array = array(), $type = 'dream'){
        if(!$array){
            return $array;
        }

        foreach($array as $key=>&$val){
            if(!$val['photo']){
                if($val['te_sex'] == 1){
                    $val['avatar'] = $type == 'dream' ? 'https://image.dttx.com/v1/tfs/T1S2_TByKT1RCvBVdK.jpg' : 'https://image.dttx.com/v1/tfs/T16O_TB4CT1RCvBVdK.jpg';
                }else {
                    $val['avatar'] = $type == 'dream' ? 'https://image.dttx.com/v1/tfs/T1q0KTB5WT1RCvBVdK.jpg' : 'https://image.dttx.com/v1/tfs/T1j2KTBCKT1RCvBVdK.jpg';
                }
            }
            $val['photo'] = $val['photo'] ? TFS_APIURL . '/' . $val['photo'] : $val['avatar'];

            if(!$val['description']){
                $val['description'] = "　";
            }
        }

        return $array;
    }
}
