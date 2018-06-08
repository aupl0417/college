<?php
/*=============================================================================
#     FileName: getCourseList.json.php
#         Desc: 根据班级ID获取课程列表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-29 20:33:13
#      History:
#      Paramer: 
=============================================================================*/
/*
 * @api                    {post} /Course/getCourseList.json 通过班级ID获取课程列表
 * @apiDescription         通过班级ID获取课程列表
 * @apiName                getCourseList_json
 * @apiGroup               Course
 *
 * @apiParam {string}     classId      班级id(可选)
 *
 * @apiSuccess (Success 1001) {Int} co_id 课程ID
 * @apiSuccess (Success 1001) {String} co_name 课程名
 *
 * @apiSampleRequest http://apicollege.wyh.com/Course/getCourseList
 *
 * @apiSuccessExample      Success-Response:
 *{
    "code": 1001,
    "msg": "获取成功",
    "data": [
        {
            "co_id": "57",
            "co_name": "大唐天下C 商业模式"
        },
        {
            "co_id": "59",
            "co_name": "电子商务"
        }
   ]
 }
 *
 * @apiErrorExample     Error-Response:
 *  {
 *       code: 1002,
         msg: "获取失败",
         data: "获取信息错误"
 }
 */

class getCourseList_json extends api {
    private $db;
    function run() {
        $options  = $this->options;
        $where    = '1';
        $this->db = new MySql();

        if (isset($options['classId']) && !empty($options['classId'])) {
            $courseList = $this->getCourseListByClass(intval($options['classId']));
        }else{
            $courseList = $this->db->getAll('SELECT * FROM tang_course WHERE co_state=1');
        }

        if (empty($courseList)) {
            return apis::apiCallback('1002', '没有课程列表'); 	
        }

        return apis::apiCallback('1001', $courseList); 	
    }

    private function getCourseListByClass($classId){
        $classCourse = $this->db->getAll('select cc_classId as classId,cc_courseId as courseId from tang_class_course where cc_classId="' . $classId . '"');
        $classCourse = array_column($classCourse, 'classId', 'courseId');
        
        $sql = 'select co_id,co_name from tang_class_table
                LEFT JOIN tang_class on cl_id=cta_classId 
                LEFT JOIN tang_course on co_id=cta_courseId 
                where ';
        
        foreach ($classCourse as $courseId=>$classId){
            $where = 'cta_courseId=' . $courseId . ' and cta_classId="' . $classId . '"';
            $courseList[] = $this->db->getRow($sql . $where);
        }
        
        if (empty($courseList)) {
            return [];
        }
        
        foreach($courseList as $key=>$val){
            if($val){
                $data[] = $val;
            }
        }

        return $data;
    }
}
