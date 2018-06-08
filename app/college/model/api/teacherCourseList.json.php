<?php
/**
 * @api                    {post} /api/teacherCourseList 教师课程列表
 * @apiDescription         教师课程列表
 * @apiName                teacherCourseList
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   @apiParam {int}        userId    学员id 
   @apiParam {int}        state     班级状态（0 报名中 1开课中 2课程结束 3全部）
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: '1001',
    data: [
        {
            id: "67",
            courseName: "test课程（2）",   //班级名称
            courseLogo: "http://192.168.3.201:80/v1/tfs/T1AaCTBXAT1RCvBVdK.jpg",   //班级LOGO
            branchName: "长沙分院",     //分院名称
            classState: "0",         //班级状态（0 报名中 1开课中 2课程结束）
            classId: "123",          //班级ID
            startTime: "2016-11-12",  //开课时间
            enrollCount: "1"          //报名人数
        },
        {
            id: "58",
            courseName: "市场运营",
            courseLogo: "http://192.168.3.201:80/v1/tfs/T1ttCTBXAT1RCvBVdK.png",
            branchName: "长沙分院",
            classId: "126",
            startTime: "2016-11-14",
            enrollCount: "3"
        }
    ]
  }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: '1002',
    data: '用户ID不能为空'
  }
 *
 */
class teacherCourseList_json extends api {

    function run() {
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002', '用户ID不能为空');
        
        $userId   = $this->options['userId'] + 0;
        $state    = isset($this->options['state']) ? $this->options['state'] + 0 : 3;
        $page     = isset($this->options['page']) ? $this->options['page'] + 0 : 1;
        $pageSize = isset($this->options['pageSize']) ? $this->options['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        $db = new MySql();
        
        //查找符合条件的班级
        $sql = 'select cl_id as classId from tang_class where cl_status=1';
        if($state == 3){
            $condition = ' and cl_state<>-1';
        }else if(in_array($state, array(0,1,2))) {
            $condition = ' and cl_state=' . $state;
        }else {
            return apis::apiCallback('1002', '班级状态参数非法');
        }
        
        $classIds = $db->getAll($sql . $condition);
        if(!$classIds){
            return apis::apiCallback('1002', '暂无班级');
        }

        //查找该教师所教的课程及课程所在班级
        $limit = 'limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        $classIds = array_column($classIds, 'classId');

        //查找符合条件的课程数据
        $field = 'cta_id as id,co_name as courseName,co_logo as courseLogo,br_name as branchName,cl_state as classState,cta_classId as classId,cta_courseId as courseId,
            (select count(tse_id) from tang_student_enroll where tse_classId=classId and tse_status<>-1 and tse_state<>-1) as enrollCount,
            cta_startTime as startTime';
        $sql = 'select ' . $field . ' from tang_class_table
               LEFT JOIN tang_class on cta_classId=cl_id
               LEFT JOIN tang_course on cta_courseId=co_id
               LEFT JOIN tang_branch on br_id=tangCollege
               where cta_teacherId="' . $userId . '" and cta_classId in (' . implode(',', $classIds) . ') ' . $limit;

        $result = $db->getAll($sql);
        if(!$result){
            return apis::apiCallback('1002', '暂无课程');
        }

        foreach($result as $key=>&$val){
            $val['courseLogo'] = TFS_APIURL . '/' . $val['courseLogo'];
        }

        apis::apiCallback('1001', $result);
    }
}
