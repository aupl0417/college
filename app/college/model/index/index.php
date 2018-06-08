<?php

class index extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {
        $bid = isset($this->options['bid']) && !empty($this->options['bid']) ? $this->options['bid'] + 0 : 2;
        $this->setHeadTag('title', '首页-唐人大学'.SEO_TITLE);
		// $this->setHeadTag('keywords', ''.SEO_KEYWORDS);
		// $this->setHeadTag('description', SEO_DESCRIPTION.'');
//        dump($_SESSION);die;
        $data = array('identityType' => -1, 'bid'=>$bid);

        if($_SESSION && $_SESSION['userID']){
            $param['userId'] = $_SESSION['userID'];
            $userInfo = apis::request('/college/api/getUserInfo.json', $param, true);
            $data['userNick'] = $_SESSION['userNick'];
            $data['userLevel'] = $_SESSION['userLevel'] == 1 ? '消费商会员' : ($_SESSION['userLevel'] == 2 ? '创客会员' : '创投会员');
            $data['score'] = 0;
            $data['level'] = '';
            $data['identityType'] = 0;
            $data['identity'] = '学员身份';
            $data['isLogin'] = 1;
            if($userInfo['code'] == '1001' && $userInfo['data']){
                $score = $this->db->getField('select SUM(cs_score) as score from tang_class_student where cs_studentId="' . $userInfo['data']['userId'] . '" and cs_isGraduated=1 and cs_status=0');
                $data['score'] = $score ? $score : 0;
                //$classId = $this->db->getAll('select cs_classId from tang_class_student where cs_studentId="' . $userInfo['data']['userId'] . '" order by cs_id desc limit 1');
                if($userInfo['data']['identityType'] == 1){
                    $level = $this->db->getField('select tl_name as teacherLevel from tang_teacher LEFT JOIN tang_teacher_level on te_level=tl_id where te_userId="' . $userInfo['data']['userId'] . '"');
                    $data['level'] = $level ? $level : '';
                }
                $data['identityType'] = $userInfo['data']['identityType'];
                $data['identity'] = $userInfo['data']['identityType'] == 1 ? '讲师身份' : '学员身份';
            }
        }

        $branchIds = $this->db->getAll('SELECT DISTINCT tangCollege from tang_class where cl_state in (1,2,3) and cl_status=1 and cl_isTest=0');
        $branchIds = array_column($branchIds, 'tangCollege');
        $branch = $this->db->getAll('select br_id as id,br_name as branchName from tang_branch where br_id in (' . implode(',', $branchIds) . ') and br_state=1');
        $headBranch = array();
        $field = 'cl_id as id, cl_name as className,br_id,cl_logo as logoUrl,cl_startTime as startTime,cl_endTime as endTime,cl_cost as enrollFee,cl_enrolledCount as enrolledCount,cl_allowableNumber as allowNumber,br_name as branchName,cl_isHot as isHot,cl_state as classState';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_status<>-1 and tse_state<>-1) as enrollCount from tang_class
               LEFT JOIN tang_branch on br_id=tangCollege
               LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id
               where ';

        $where = 'cl_state in (0,1,2) and cl_status=1 and cl_isTest=0 and br_id="' . $bid .'"';
        $order = ' order by cl_createTime desc';
        $limit = ' limit 0,6';
        
        //总院班级
        $headBranchClass = $this->db->getAll($sql . $where . $order . $limit);
        $headBranchClass = $this->arrangeClassInfo($headBranchClass);
        if($headBranchClass){
            foreach($headBranchClass as $key=>&$val){
                $enrollState = 0;
                if($_SESSION['userID']){
                    $enrollState = $this->db->getField('select count(tse_id) from tang_student_enroll LEFT JOIN tang_ucenter_member on tse_userId=id where userId="' . $_SESSION['userID'] . '" and tse_classId="' . $val['id'] . '" and tse_state<>-1 and tse_status<>-1');
                }
                $val['enrollState'] = $enrollState;
                if($val['classState'] == 2){
                    $val['enrollState'] = -1;//如果班级已结束，则报名状态改为已结束
                }
            }
        }
        
        //名师风采(测试账号不显示)
        $tfield = 'te_id as id,trueName as teacher,username,te_photo as photo,te_sex,te_description as description,avatar';
        $tsql  = 'select ' . $tfield . ' from tang_teacher LEFT JOIN tang_ucenter_member on id=te_userId where identityType=1 and te_userId not in (650,645,133) and te_isLeave=0 order by te_level desc limit 0,4';
        $teacherList = $this->db->getAll($tsql);
        $teacherList = $this->arrangeTeacherInfo($teacherList, 'teacher');
        
        //推荐班级
        $recommentClass = $this->db->getAll('select cl_id as id,cl_name as className,cl_logo as logoUrl from tang_class where cl_state=0 and cl_status=1 and cl_isTest=0 order by cl_id desc limit 4');
        foreach($recommentClass as &$val){
            $val['logoUrl'] = TFS_APIURL . '/' . $val['logoUrl'];
        }

        //学员风采
        $glimpseList = array();
        /*$classIds = $this->db->getAll('select cl_id as id from tang_class where cl_id not in (135,136,138,170) and cl_state<>-1 and cl_status=1 and cl_isTest=0 order by cl_id desc');
        $classIds = array_column($classIds, 'id');
        foreach($classIds as $key => $classId){
            $glimpse = $this->db->getAll('select tcp_classId as id,tcp_filename as filename from tang_class_picture where tcp_classId="' . $classId . '" order by tcp_isLogo desc,tcp_sort asc limit 2');
            if(!$glimpse){
                $glimpse[0] = $this->db->getRow('select cl_id as id,cl_logo as filename from tang_class WHERE cl_id="' . $classId . '"');
            }
            $glimpseList[$key] = count($glimpse) > 1 ? $glimpse[1] : $glimpse[0];
        }*/
        $glimpseList = $this->db->getAll('select tcp_classId as id,tcp_filename as filename from tang_class_picture where tcp_classId="153" order by tcp_isLogo desc,tcp_sort asc');

        if($glimpseList){
            foreach($glimpseList as &$val){
                $val['filename'] = TFS_APIURL . '/' . $val['filename'];
            }
        }

        $this->setReplaceData($data);
        $this->setLoopData('branchList', $branch);
        $this->setLoopData('headBranch', $headBranchClass);
        $this->setLoopData('teacherList', $teacherList);
        $this->setLoopData('glimpseList', $glimpseList);
        $this->setLoopData('recommentClass', $recommentClass);
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
            $v['className'] = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $v['className']);
            $v['logoUrl']      = TFS_APIURL.'/'.$v['logoUrl'];
            $v['enrollNum']    = intval($enrollNum[$v['id']]);
            $v['enrollCount'] += $v['enrolledCount'];
            $course = $this->db->getAll('select cc_classId,cc_courseId,co_name as courseName from tang_class_course LEFT JOIN tang_course on co_id=cc_courseId where cc_classId=' . $v['id'] . ' and co_state=1 limit 2');
            
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
