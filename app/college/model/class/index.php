<?php

class index extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {
        $state = isset($this->options['state']) ? $this->options['state'] + 0 : 3;
        $sort = isset($this->options['sort']) ? $this->options['sort'] : 'latest';
        $bid  = isset($this->options['bid']) ? $this->options['bid'] + 0 : 2;

        $field = 'cl_id as id,cl_name as className,cl_startTime as startTime,cl_cost as enrollFee,cl_logo as classLogo';
        if($sort == 'complex'){
            $field .= ',(select AVG(tc_level) from tang_teacher_comment where tc_classId=id) as level';
            $order = 'order by level desc';
        }else if($sort == 'price'){
            $order = 'order by cl_cost desc';
        }else {
            $order = 'order by cl_createTime desc';
        }

        $where = 'where cl_status=1 and cl_state<>-1 and cl_isTest=0 and tangCollege="' . $bid . '"';
        if(in_array($state, array(0, 1, 2))){
            $where .= ' and cl_state="' . $state . '"';
        }

        $page = new page('tang_class', $field, $this->options['page'] + 0, 12, 0, $where, $order);
        $page->getDivPage();
        $result = $page->getPage();

        foreach($result as $k=>&$v){
            $logo = '';
            if(!$v['classLogo']){
                $logo = $this->db->getField('select filename from tang_class_picture where tcp_classId="' . $v['id'] .'" and tcp_isLogo=1');
            }
            $v['className'] = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $v['className']);
            $v['classLogo'] = $v['classLogo'] ? TFS_APIURL . '/' . $v['classLogo'] : ($logo ? $logo : '');
            $course = $this->db->getAll('select cc_classId,cc_courseId,co_name as courseName from tang_class_course LEFT JOIN tang_course on co_id=cc_courseId where cc_classId=' . $v['id'] . ' and co_state=1 limit 2');
//            dump($course);die;
            $v['content']   = '';
            if($course){
                $v['content']  = '第1课：' . $course[0]['courseName'];
                $v['content'] .= $course[1] ? "<br/>" . '第2课：' . $course[1]['courseName'] : '';
            }
        }

        $branchIds = $this->db->getAll('SELECT DISTINCT tangCollege from tang_class where cl_state in (1,2,3) and cl_status=1 and cl_isTest=0');
        $branchIds = array_column($branchIds, 'tangCollege');
        $branchList = $this->db->getAll('select br_id as id,br_name as branchName from tang_branch where br_id in (' . implode(',', $branchIds) . ') and br_state=1');

        $this->setHeadTag('title', '班级首页-唐人大学'.SEO_TITLE);
        $this->setReplaceData('pagelist',$page->getMenu());
        $this->setReplaceData(['state'=>$state, 'sort'=>$sort, 'menu'=>$this->options['PATH_MODEL'], 'bid' =>$bid]);
        $this->setLoopData('classList', $result);
        $this->setLoopData('branchList', $branchList);
		$this->setTempAndData();
        $this->show();
    }
}
