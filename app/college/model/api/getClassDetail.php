<?php
/**
 * @api                    {get} /api/getClassDetail 班级详情
 * @apiDescription         获取班级详情
 * @apiName                detail
 * @apiGroup               Class
 * @apiPermission          aupl
 *
 * @apiParam {int}   	  id        班级id
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
"code": "1001",
    "data": {
        "id": "136",
        "className": "test班级（16）",
        "logoUrl": "http://192.168.3.201:80/v1/tfs/T1sRCTBXLT1RCvBVdK.jpg",
        "startTime": "2016/11/18",
        "endTime": "2016/11/19",
        "classStartTime": "2016/11/20",
        "classEndTime": "2016/11/23",
        "catering": "两正餐二早",
        "hostel": "大唐之家双人间",
        "conditions": "所有学员",
        "enrollFee": "100.00",
        "allowNumber": "10",
        "state": "0",
        "status": "1",
        "branchName": "邵阳分院",
        "descriptions": " 天天天天天天天天</p>",
        "enrollCount": "2",
        "enrollInfo": "2/10报名",
        "enrollStatus": -2,
        "enrollState": -2,
        "isCollect": 0,
        "courseList": [
                {
                "cid": "224",
                "courseName": "电子商务",
                "descriptions": "1231",
                "startTime": "11月20日 09:00",
                "endTime": "11月20日 10:00",
                "teacherName": "ceshi1",
                "trainAddress": "测试",
                "desc": "1231"
                },
                {
                "cid": "225",
                "courseName": "电子商务",
                "descriptions": "1231",
                "startTime": "11月20日 09:00",
                "endTime": "11月20日 10:00",
                "teacherName": "ceshi1",
                "trainAddress": "测试",
                "desc": "1231"
                }
        ]
    }
}
 *
 * @apiErrorExample        Error-Response:
    {
        "code": "1002",
        "data": "暂无班级信息"
    }
 *
 */
class getClassDetail extends api{

    private $db;


    function run() {
        $this->db=new MySql();
        if(!isset($this->options['id']) || empty($this->options['id'])){
            return apis::apiCallback('1002','班级ID不能为空!');
        }
        
        $id = $this->options['id'] + 0;//班级id
        $field = 'cl_id as id, cl_name as className,cl_logo as logoUrl,cl_enrollStartTime as startTime,cl_enrollEndTime as endTime,cl_startTime as classStartTime,cl_endTime as classEndTime,cl_catering as catering,cl_hostel as hostel,cl_condition as conditions,cl_cost as enrollFee,cl_allowableNumber as allowNumber,cl_state as state,cl_status as status,br_name as branchName,cl_description as descriptions';
        $sql = 'select ' . $field . ',(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_state<>-1 and tse_status<>-1) as enrollCount from tang_class 
               LEFT JOIN tang_branch on br_id=tangCollege 
               LEFT JOIN tang_trainingsite on cl_defaultTrainingsiteId=tra_id 
               LEFT JOIN tang_area on a_code=tra_areaId 
               where cl_state<>-1 and cl_status=1 and cl_id="' . $id . '"';
        
        $result = $this->db->getRow($sql);

        if (empty($result)){
            return apis::apiCallback('1002', '暂无班级信息');

        }

        $sql = "SELECT at_value FROM tang_attrib WHERE at_key='%d' AND at_type='%d'";
        
        $result['hostel']   = $this->db->getField(sprintf($sql,$result['hostel'],2));
        $result['catering'] = $this->db->getField(sprintf($sql,$result['catering'],1));
        
        $studyDirection = $this->db->getAll("SELECT stc_name FROM tang_study_condition WHERE stc_id IN({$result['conditions']})");
        $result['conditions'] = join(' | ',array_column($studyDirection,'stc_name'));
        $result['enrollInfo']  = $result['enrollCount'] . '/' . $result['allowNumber'] . '报名';
        $result['logoUrl'] = !empty($result['logoUrl'])? TFS_APIURL.'/'.$result['logoUrl'] :"";
        $result['enrollStatus'] = -2;
        $result['enrollState']  = -2;
        $result['isCollect']    =  0;
        $result['descriptions']    =  F::TextToHtml($result['descriptions']);
        $result['startTime']    =  date('Y/m/d', strtotime($result['startTime']));;
        $result['endTime']    =  date('Y/m/d', strtotime($result['endTime']));;
        $result['classStartTime']    =  date('Y/m/d', strtotime($result['classStartTime']));;
        $result['classEndTime']    =  date('Y/m/d', strtotime($result['classEndTime']));;

        if(isset($this->data['userId']) && !empty($this->data['userId'])){
            $enrollInfo = $this->db->getRow('select tse_id,tse_status,tse_state from tang_student_enroll where tse_userId="' . $this->uid .'" and tse_classId="' . $id .'" order by tse_id desc limit 1');
            if($enrollInfo){
                $result['enrollStatus'] = $enrollInfo['tse_status'];
                $result['enrollState']  = $enrollInfo['tse_state'];
            }
            $count = $this->db->getField('select count(tcc_id) from tang_class_collection where tcc_userId="' . $this->uid . '" and tcc_classId="' . $id . '" and tcc_deviceId="' . $this->data['deviceID'] . '"');
            if($count){
                $result['isCollect'] = 1;
            }
        }
        
        $courseIds = $this->db->getAll('select cc_courseId from tang_class_course where cc_classId="' . $id . '"');
        $courseIds = array_column($courseIds, 'cc_courseId');
        
        $courseList = array();
        if($courseIds){
            $sql = 'select cta_id as cid,co_name as courseName, co_description as descriptions,cta_startTime as startTime,cta_endTime as endTime,username as teacherName,tra_address as trainAddress from tang_class_table
               LEFT JOIN tang_ucenter_member on cta_teacherId=id
               LEFT JOIN tang_course on co_id=cta_courseId
               LEFT JOIN tang_trainingsite on tra_id=cta_trainingsiteId
               where cta_courseId in (' . implode(',', $courseIds) . ') and cta_classId="' . $id . '"';
            
            $courseList = $this->db->getAll($sql);
            
            foreach ($courseList as $key=>&$val) {
                $val['startTime'] = date('m月d日 H:i', strtotime($val['startTime']));
                $val['endTime'] = date('m月d日 H:i', strtotime($val['endTime']));
                $val['descriptions'] = trim($val['descriptions']);
                $val['desc'] = $val['descriptions'];
       //         $val['descriptions'] = explode("\n", $val['descriptions']);
            }
        }
        
        $result['courseList'] = $courseList;

        return apis::apiCallback('1001', $result);
    }
    
}
