<?php
/*
 * 签到记录
 * param $userId 用户Id
 * */
class record extends guest{
    private $db;
    
    function __construct($options) {
        header("Content-type:text/html;charset=utf-8");
        parent::__construct($options);
		$this->db = new MySql();
		$this->data = $options;
    }
    
	function run(){
	    $this->setHeadTag('title', '签到记录-C+商业系统');
		$userId = $this->data['userId'];
		
		empty($userId) && die($this->show(message::getJsonMsgStruct('1002',  '用户id不能为空'))); //用户id必填
		
		$uid = $this->db->getField('select id from tang_ucenter_member where userId="' .$userId . '"');
		!$uid && die($this->show(message::getJsonMsgStruct('1002',  '用户不存在')));
		
		$field = 'att_id,att_userId,att_classTableId,att_createTime,cta_classId,cta_courseId,co_name as courseName,cta_startTime,cta_endTime,tra_name';
		$sql = 'select ' . $field . ' from tang_attendance 
		       LEFT JOIN tang_ucenter_member on att_userId=id 
		       LEFT JOIN tang_class_table on cta_id=att_classTableId 
		       LEFT JOIN tang_class on cta_classId=cl_id 
		       LEFT JOIN tang_course on cta_courseId=co_id 
		       LEFT JOIN tang_trainingsite f on cta_trainingsiteId = tra_id 
		       where att_userId="'. $uid . '"';
		
		$Atten = $this->db->getAll($sql);
		$classTableIds = array_column($Atten, 'att_classTableId');
		$classTableIds = array_unique($classTableIds);
		$attendData = $this->attendDataHandle($Atten, $classTableIds);
		
        $data['app'] = isset($this->data['app']) ? $this->data['app'] + 0 : 0;
		
		$this->setLoopData('attendanceList',$attendData);
		$this->setReplaceData($data);
		$this->setTempAndData();
		$this->show();
	}
	
/*
     * 将同一课时的签到数据放到一起
     * param $Atten 签到记录数据
     * param $classTableIds 课时id集
     * return array;
     * */
    private function attendDataHandle($Atten, $classTableIds){
        if(empty($Atten) || empty($classTableIds)){
            return array();
        }
        $i = 0;
        foreach($classTableIds as $key=>$val){
            foreach($Atten as $k=>$v){
                if($val == $v['att_classTableId']){
                    $data[$i][] = $v;
                }
            }
            $i++;
        }
        $data = $this->mergeAttenByClassTableId($data);
        return $data;
    }
    
    /*
     * 将同一课时的签到组合到同一数组
     * array(
     *  'att_classTableId'
     *  'cta_courseId'
     *  'courseName'
     *  'tra_name',
     *  'date',
     *  'attendTime' => array(
     *          array('attendStartTime','attendEndTime')
     *   )
     * );
     * */
    private function mergeAttenByClassTableId($data){
        if(!is_array($data) || empty($data)){
            return array();
        }
        
        foreach($data as $key=>$val){
            $createTime = array_column($val, 'att_createTime');
            $result[$key]['att_classTableId'] = $val[0]['att_classTableId'];
            $result[$key]['cta_courseId'] = $val[0]['cta_courseId'];
            $result[$key]['courseName'] = $val[0]['courseName'];
            $result[$key]['tra_name'] = $val[0]['tra_name'];
            $result[$key]['date'] = date('Y-m-d', strtotime($val[0]['cta_startTime']));
            
            $attendTime = $this->arrangeAttendTime($createTime, $val[0]['cta_startTime'], $val[0]['cta_endTime']);
            $result[$key]['attendStartTime'] = $attendTime['attendStartTime'];
            $result[$key]['attendEndTime'] = $attendTime['attendEndTime'];
        }
        
        return $result;
    }
    
    /*
     * 判断是签到时间，还是签退时间，漏签判断及其初始化
     * */
    private function arrangeAttendTime($createTime, $startTime, $endTime){
        $startTime = strtotime($startTime);//上课时间
        $endTime = strtotime($endTime);     //下课时间
        $attendTime = array();
        if(count($createTime) == 1){
            if(strtotime($createTime[0]) < $startTime){
                $attendTime = array('attendStartTime'=>$createTime[0], 'attendEndTime'=>'00:00:00');
            }else if(strtotime($createTime[0]) > $endTime) {
                $attendTime = array('attendStartTime'=>'00:00:00', 'attendEndTime'=>$createTime[0]);
            }
        }else{
            if(strtotime($createTime[0]) < strtotime($createTime[1])){
                $attendTime = array('attendStartTime'=>$createTime[0], 'attendEndTime'=>$createTime[1]);
            }else {
                $attendTime = array('attendStartTime'=>$createTime[1], 'attendEndTime'=>$createTime[0]);
            }
        }
        
        return $attendTime;
    }
}
