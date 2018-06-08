<?php

class detail extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {
        if(!isset($this->options['id']) || empty($this->options['id'])){
            $url = U('index/index');
            header('location:' . $url);
        }
        $classId = $this->options['id'] + 0;
        $this->setHeadTag('title', '学员风采-相册列表页'.SEO_TITLE);
		// $this->setHeadTag('keywords', ''.SEO_KEYWORDS);
		// $this->setHeadTag('description', SEO_DESCRIPTION.'');
        $classInfo = $this->db->getRow('select cl_name as className,cl_logo as filename from tang_class where cl_id="' . $classId . '"');
        $className = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $classInfo['className']);
        
        //学员风采
        $glimpseList = $this->db->getAll('select tcp_filename as filename from tang_class_picture where tcp_classId="' . $classId . '" order by tcp_isLogo desc,tcp_sort desc');
        if($glimpseList){
            foreach($glimpseList as &$val){
                $val['filename'] = TFS_APIURL . '/' . $val['filename'];
            }
        }else {
            $glimpseList[0]['filename'] = TFS_APIURL . '/' . $classInfo['filename'];
        }
        
        $this->setReplaceData(['className' => $className, 'menu' => $this->options['PATH_MODEL']]);
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

//        $courseList = $this->db->getAll('select cc_classId,cc_courseId,co_name as courseName from tang_class_course LEFT JOIN tang_course on co_id=cc_courseId where cc_classId IN (' . $classIds . ') and co_state=1');
//        dump($courseList);die;
        foreach ($class as &$v) {
            $v['logoUrl']   = TFS_APIURL.'/'.$v['logoUrl'];
            $v['enrollNum'] = intval($enrollNum[$v['id']]);
            $course = $this->db->getAll('select cc_classId,cc_courseId,co_name as courseName from tang_class_course LEFT JOIN tang_course on co_id=cc_courseId where cc_classId=' . $v['id'] . ' and co_state=1 limit 2');
//            dump($course);die;
            $v['content']   = '';
            if($course){
                $v['content']  = '第1课：' . $course[0]['courseName'];
                $v['content'] .= $course[1] ? "<br/>" . '第2课：' . $course[1]['courseName'] : '';
            }
        }

        return $class;
    }

    private function getUser($userId){
        $params['input'] = $userId;
        $path = '/user/getUser';
        $sdk = new openSdk();
        $result = $sdk->request($params, $path);

        if(!is_array($result))  return false;
        if($result['id'] != 'SUCCESS' && $result['id'] != 'SUCCESS_EMPTY')  return false;

        $userInfo = $result['info'];
        if(!$userInfo) return false;

        return $userInfo;
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
