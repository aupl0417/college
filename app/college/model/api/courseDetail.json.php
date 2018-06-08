<?php
/**
 * @api                    {post} /Course/courseDetail.json 课程详情
 * @apiDescription         课程详情
 * @apiName                courseDetail_json
 * @apiGroup               Course
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {int}        courseId   课程id
   @apiParam {int}        classId    班级id 
 *
 *
 * @apiSuccessExample      Success-Response:
 *{//学员端调用示例
    code: 1001,
    msg: "操作成功",
    data: {
        id: "65",
        courseName: "联盟商家展业培训",
        logoUrl: "https://image.dttx.com/v1/tfs/T15EKTB7ZT1RCvBVdK.jpg",
        descriptions: "1.C+商业模式剖析 2.C+商业模式收益分配 3.解读经典案例 4.如何成为联盟商家 项目部对接说明",
        content: "1.C+商业模式剖析 2.C+商业模式收益分配 3.解读经典案例 4.如何成为联盟商家 项目部对接说明",
        credit: "10",
        hour: "10",
        classType: "电子商务",
        gradeName: "初级"
    }
   }
 *
 *
 *@apiSuccessExample      Success-Response:
 *{ //教师端调用示例 
    code: 1001,
    msg: "操作成功",
    data: {
        courseName: "test课程（2）",  //课程名称
        logo: "http://192.168.3.201:80/v1/tfs/T1AaCTBXAT1RCvBVdK.jpg",  //课程LOGO
        branch: "长沙分院",   //分院
        startTime: "2016-11-12",   //开课开始时间
        endTime: "2016-11-14",     //开课结束时间
        classState: "2",           //班级状态（0 报名中 1开课中 2课程结束）
        className: "test班级(4)",   //班级名称
        credit: "10",              //学分
        courseType: "test",        //课程分类
        courseGrade: "初级",        //课程等级
        hour: "10",                //课时
        descriptions: "test课程（2） 课程描述",     //课程描述
        content: "test课程（2） 课程说明",          //课程说明
        trainingSite: "308会议室",              //场地
        teachReword: "100.00",                //授课费用
        enrollCount: "1"                      //报名人数
    }
   }
 */
class courseDetail_json extends api{
	
    function run() {
        if(!isset($this->options['courseId']) || empty($this->options['courseId'])) return apis::apiCallback('1002', '课程ID不能为空');
        
        $courseId = $this->options['courseId'] + 0;
        $classId  = isset($this->options['classId']) ? $this->options['classId'] + 0 : 0;
        $userId   = isset($this->options['userId'])  ? $this->options['userId']  + 0 : 0;
        
        $db = new MySql();
        
        if($classId){
            $field = 'co_name as courseName,co_logo as logo, br_name as branch,cta_startTime as startTime,cta_endTime as endTime,cl_state as classState,cl_name as className,cc_credit as credit,sd_name as courseType,gr_name as courseGrade,cc_hour as hour,co_description as descriptions,co_content as content';
            $sql = 'select ' . $field . ' from tang_class_table
                   LEFT JOIN tang_course on cta_courseId=co_id 
                   LEFT JOIN tang_class_course on co_id=cc_courseId 
                   LEFT JOIN tang_class on cl_id=cc_classId 
                   LEFT JOIN tang_branch on br_id=tangCollege 
                   LEFT JOIN tang_study_direction on co_studyDirectionId=sd_id 
                   LEFT JOIN tang_grade on gr_id=co_gradeID 
                   where cta_id="' . $courseId . '" and cc_classId="' . $classId . '" and cl_state<>-1 and cl_status=1';

            $classCourse = $db->getRow($sql);
            if(!$classCourse){
                return apis::apiCallback('1002', '暂无该课程信息');
            }
            
            $classTableInfo = $db->getRow('select tra_name,te_courseReward from tang_class_table left join tang_trainingsite on cta_trainingsiteId=tra_id LEFT JOIN tang_teacher on cta_teacherId=te_userId where cta_classId="' . $classId . '" and cta_id="' . $courseId . '" limit 1');
            $classCourse['trainingSite'] = $classTableInfo['tra_name'];
            $classCourse['teachReword'] = $classTableInfo['te_courseReward'];
            $classCourse['enrollCount'] = $db->getField('select count(tse_id) from tang_student_enroll where tse_classId="' . $classId . '" and tse_status<>-1 and tse_state<>-1');
            $classCourse['logo'] = TFS_APIURL . '/' . $classCourse['logo'];
            $classCourse['minutes'] = $classCourse['hour'] * 60;

            return apis::apiCallback('1001', $classCourse);
        }else {
            $field = 'co_id as id,co_name as courseName,co_logo as logoUrl,co_description as descriptions,co_content as content,co_credit as credit,co_hour as hour,sd_name as classType,gr_name as gradeName';
            $sql = 'select ' . $field . ' from tang_course
               LEFT JOIN tang_study_direction on co_studyDirectionId=sd_id
               LEFT JOIN tang_grade on gr_id=co_gradeID
               where co_state=1 and co_id="' . $courseId . '"';
            
            $course = $db->getRow($sql);
            if(!$course) return apis::apiCallback('1002', '暂无课程');
            
            $course['descriptions'] = trim($course['descriptions']);
            $course['content']      = trim($course['content']);
            $course['logoUrl']      = TFS_APIURL . '/' . $course['logoUrl'];
            
            return apis::apiCallback('1001', $course);
        }
        
    }
}
