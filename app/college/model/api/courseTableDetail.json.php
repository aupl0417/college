<?php
/**
 * @api                    {post} /api/courseTableDetail.json 讲师备课表详情
 * @apiDescription         讲师备课表详情
 * @apiName                courseTableDetail_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   @apiParam {int}        classId   班级ID 
   @apiParam {int}        userId    教师ID
   @apiParam {string}     userId    教师ID
   @apiParam {int}        year      年
   @apiParam {int}        month     月
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        week: "2016-11-17 星期四",
        course: [
            {
                className: "test班级(13)",
                courseName: "线性代数20",
                startTime: "10:00",
                endTime: "11:00"
            },
            {
                className: "test班级（14）",
                courseName: "高等数学",
                startTime: "10:00",
                endTime: "11:00"
            }
        ]
    }
  }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: '1002',
    data: '用户ID不能为空'
  }
 *
 */
class courseTableDetail_json extends api {

    function run() {
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002', '用户ID不能为空');
        if(!isset($this->options['year'])   || empty($this->options['year']))   return apis::apiCallback('1002', '年份不能为空');
        if(!isset($this->options['month'])  || empty($this->options['month']))  return apis::apiCallback('1002', '月份不能为空');
        if(!isset($this->options['day'])    || empty($this->options['day']))    return apis::apiCallback('1002', '天不能为空');
        
        $userId   = $this->options['userId'] + 0;
        $year     = $this->options['year']   + 0;
        $month    = $this->options['month']  + 0;
        $day      = $this->options['day']    + 0;
        
        $db = new MySql();
        
        if($day < 10){
            $day = '0' . $day;
        }
        $month = $month >= 10 ? $month : '0' . $month;
        $date = $year . '-' . $month . '-' . $day;
        if(!$this->checkDateIsValid($date, array('Y-m-d'))){
            return apis::apiCallback('1002', '日期参数不正确');
        };
        
        $weekDay = date('w', strtotime($date));
        $week    = array('日', '一', '二', '三', '四', '五', '六');
//         $data['week'] = $date . ' 星期' . $week[$weekDay];
//         dump($data);die;
        $beginDate = $date . ' 00:00:00';
        $endDate   = $date . ' 23:59:00';
        $sql = 'select cl_name as className,co_name as courseName,cta_startTime as startTime,cta_endTime as endTime from tang_class_table 
               LEFT JOIN tang_course on co_id=cta_courseId 
               LEFT JOIN tang_class on cl_id=cta_classId 
               LEFT JOIN tang_grade on gr_id=cl_gradeID 
               where cta_startTime>="' . $beginDate . '" and cta_endTime<="'. $endDate . '" and cta_teacherId="' . $userId . '" and cl_status=1';
        
        $classTable = $db->getAll($sql);
        if(!$classTable){
            return apis::apiCallback('1002', '今天没课');
        }
        
        foreach ($classTable as $key=>&$val){
            $val['startTime'] = date('H:i', strtotime($val['startTime']));
            $val['endTime'] = date('H:i', strtotime($val['endTime']));
        }
        
        $data = array(
            'week' => $date . ' 星期' . $week[$weekDay],
            'course' => $classTable
        );
        
        apis::apiCallback('1001', $data);
    }
    
    //日期格式检测
    private function checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d")) {
        $unixTime = strtotime($date);
        if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
            return false;
        }
        //校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }
    
        return false;
    }
}
