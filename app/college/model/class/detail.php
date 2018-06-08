<?php

class detail extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {
//        (!isset($this->options['id'])) || empty($this->options['id']) && die($this->show(message::getJsonMsgStruct('1002', '参数非法')));
        $id = $this->options['id'] + 0;
        $this->setHeadTag('title', '班级详情-唐人大学'.SEO_TITLE);
		// $this->setHeadTag('keywords', ''.SEO_KEYWORDS);
		// $this->setHeadTag('description', SEO_DESCRIPTION.'');
		
        $result = array();
        $field = 'cl_id as id, cl_name as className,cl_logo as logoUrl,cl_enrollStartTime as startTime,cl_enrollEndTime as endTime,cl_startTime as classStartTime,cl_endTime as classEndTime,
                cl_catering as catering,cl_hostel as hostel,cl_condition as conditions,cl_headmasterId,cl_cost as enrollFee,cl_enrolledCount as enrolledCount,cl_allowableNumber as allowNumber,
                cl_state as state,cl_status as status,br_name as branchName,cl_description as descriptions,tra_name as trainAddress';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_state<>-1 and tse_status<>-1) as enrollCount from tang_class
               LEFT JOIN tang_branch on br_id=tangCollege
               LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id
               LEFT JOIN tang_area on a_code=tra_areaId
               where cl_state<>-1 and cl_status=1 and cl_id="' . $id . '"';

        $result = $this->db->getRow($sql);

        if(!$result){
            header('Location:' . U('college/class/index'));
        }
        
        $result['className'] = str_replace('C ＋', 'C＋', $result['className']);
        $result['className'] = str_replace('C ', 'C＋', $result['className']);
        $result['className'] = str_replace('C＋', 'C<sup>＋</sup>', $result['className']);
        $result['enrollCount'] += $result['enrolledCount'];
        $result['logoUrl'] = TFS_APIURL . '/' . $result['logoUrl'];
        $result['days']    = (strtotime($result['classEndTime'] . ' 24:00:00') - strtotime($result['classStartTime'] . ' 00:00:00')) / (60*60*24);
        $result['descriptions'] = F::TextToHtml($result['descriptions']);
        $result['descriptions'] = str_replace('&nbsp;', '', $result['descriptions']);
        $result['descriptions'] = str_replace('c ', 'C<sup>+</sup>', $result['descriptions']);
        $result['descriptions'] = str_replace('C ', 'C<sup>+</sup>', $result['descriptions']);
        $result['descriptions'] = str_replace("<p>", "", $result['descriptions']);
        $result['descriptions'] = str_replace("</p>", "", $result['descriptions']);
        $result['descriptions'] = F::TextToHtml($result['descriptions']);
        $result['total'] = $this->db->getField('select count(cs_id) from tang_class_student where cs_classId<>"' . $id . '"');
        $sum = $this->db->getField('select SUM(cl_enrolledCount) FROM tang_class WHERE cl_id<>"' . $id . '"');
        $result['total'] += $sum;

        $count = $this->db->getField('select count(tcc_id) from tang_class_collection where tcc_classId="' . $id .'" and tcc_userId=(select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '")');
        $result['isCollect'] = $count ? 1 : 0;
        
        $coField = 'co_name as courseName,avatar,cta_teacherId as teacherId,trueName as teacher,cta_startTime as startTime,cta_endTime as endTime,co_description as descriptions,co_content as content';
        $csql = 'select ' . $coField . ' from tang_class_table LEFT JOIN tang_course on co_id=cta_courseId
                LEFT JOIN tang_ucenter_member on id=cta_teacherId
                where cta_classId="' . $id . '" order by cta_startTime asc';

        $courseList = $this->db->getAll($csql);

        foreach($courseList as $key=>&$val){
            $val['className'] = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $val['className']);
            $val['avatar'] = $val['avatar'] ?: 'https://image.dttx.com/v1/tfs/T1AddTByYT1RCvBVdK.png';
            $val['startTime'] = date('H:i', strtotime($val['startTime']));
            $val['endTime']   = date('H:i', strtotime($val['endTime']));
        }
        $provinceList = $this->db->getAll("select a_id as id, a_name as name from tang_area where a_fkey=0");
        $teacherIds = array_column($courseList, 'teacherId');
        $teacherIds = array_unique($teacherIds);
        $tsql = 'select trueName as teacher,mobile,avatar,te_photo as photo,te_description as description,tl_name as level,te_sex from tang_ucenter_member
                 LEFT JOIN tang_teacher on id=te_userId 
                 LEFT JOIN tang_teacher_level on te_level=tl_id';

        $teacherInfo = array();
        if($teacherIds){
            $twhere = ' where id in (' . implode(',', $teacherIds) . ')';
            $teacherInfo = $this->db->getAll($tsql . $twhere);
            if($teacherInfo){
                foreach ( $teacherInfo as &$val ) {
                    $val['avatar'] = $val['photo'] ? TFS_APIURL . '/' . $val['photo'] : ($val['avatar'] ?: 'https://image.dttx.com/v1/tfs/T1TUDTByWT1RCvBVdK.jpg');
                }
            }
        }

        $result['enrollState'] = 0;
        if($_SESSION['userID']){
            $count = $this->db->getField('select count(tse_id) from tang_student_enroll LEFT JOIN tang_ucenter_member on tse_userId=id where tse_classId="' . $id . '" and tse_state<>-1 and tse_status<>-1 and userId="' . $_SESSION['userID'] . '"');
            if($count){
                $result['enrollState'] = 1;
            }
        }

        $map = ' where id="' . $result['cl_headmasterId'] . '"';
        $headMaster = $this->db->getRow($tsql . $map);
        $headMaster['photo'] = $this->getAvatar($headMaster['photo'], $headMaster['avatar'], $headMaster['te_sex']);

        $commentList = array();
        $commentList = $this->db->getAll('select username,avatar,tc_content as content,tc_createTime as createTime,tc_level as level from tang_teacher_comment LEFT JOIN tang_ucenter_member on id=tc_userId where tc_classId="' . $id . '" order by tc_createTime desc');
        $result['totalCount'] = 0;
        $result['totalAver']= 0.0;
        $result['goodAver'] = 0.0;
        $result['midAver']  = 0.0;
        $result['badAver']  = 0.0;
        if($commentList){
            $result['totalCount'] = $this->db->getField('select count(distinct(tc_userId)) from tang_teacher_comment where tc_classId="' . $id . '"');
            $avgSql = 'select AVG(tc_level) from tang_teacher_comment where tc_classId="' . $id . '"';
            $totalAver = $this->db->getField($avgSql);
            $goodAver  = $this->db->getField($avgSql . ' and tc_level in (4,5)');
            $midAver   = $this->db->getField($avgSql . ' and tc_level=3');
            $badAver   = $this->db->getField($avgSql . ' and tc_level<3');
            $result['totalAver'] = round($totalAver, 1);
            $result['goodAver'] = round($goodAver, 1);
            $result['midAver'] = round($midAver, 1);
            $result['badAver'] = round($badAver, 1);
            $result['totalAverStr'] = '';
            
            
            foreach($commentList as &$val){
                $val['avatar'] = $val['avatar'] ?: 'https://image.dttx.com/v1/tfs/T1AddTByYT1RCvBVdK.png';
            }
        }

        if(ceil($result['totalAver']) > 0){
            for($i=0; $i<ceil($result['totalAver']); $i++){
                $result['totalAverStr'] .= ' <i class="glyphicon glyphicon-star"></i> ';
            }
        }else{
            $result['totalAverStr'] = ' <i class="glyphicon glyphicon-star"></i> ';
        }
        
        $hsql = 'select cl_id as id,cl_name as className,cl_logo as classLogo,cl_startTime as startTime,(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_state<>-1 and tse_status<>-1) as enrollCount from tang_class where cl_state=0 and cl_status=1 and cl_isTest=0 order by cl_id desc limit 3';
        $hotList = $this->db->getAll($hsql);

        if($hotList){
            foreach($hotList as &$val){
                $val['classLogo'] = $val['classLogo'] ? TFS_APIURL . '/' . $val['classLogo'] : '';
            }
        }

        $this->setReplaceData($result);
        $this->setReplaceData($headMaster);
        $this->setLoopData('result', $result);
        $this->setLoopData('provinceList', $provinceList);
        $this->setLoopData('courseList', $courseList);
        $this->setLoopData('teacherList', $teacherInfo);
        $this->setLoopData('commentList', $commentList);
        $this->setLoopData('hotList', $hotList);
		$this->setTempAndData();
        $this->show();
    }

    function getAvatar($photo, $avatar, $sex){
        if($sex == 1){
            $photo = $photo ? TFS_APIURL . '/' . $photo : ($avatar ?: 'https://image.dttx.com/v1/tfs/T16O_TB4CT1RCvBVdK.jpg');
        }else {
            $photo = $photo ? TFS_APIURL . '/' . $photo : ($avatar ?: 'https://image.dttx.com/v1/tfs/T1j2KTBCKT1RCvBVdK.jpg');
        }
        
        return $photo;
    }
    
}
