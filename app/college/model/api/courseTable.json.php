<?php
/**
 * @api                    {post} /api/courseTable.json 讲师备课表 
 * @apiDescription         讲师备课表
 * @apiName                courseTable_json
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
    data: [
        {
            day: 1,
            courseCount: 0
        },
        {
            day: 2,
            courseCount: 0
        },
        {
            day: 3,
            courseCount: 0
        },
        {
            day: 4,
            courseCount: 0
        },
        {
            day: 5,
            courseCount: 0
        },
        {
            day: 6,
            courseCount: 0
        },
        {
            day: 7,
            courseCount: 0
        },
        {
            day: 8,
            courseCount: 0
        },
        {
            day: 9,
            courseCount: 0
        },
        {
            day: 10,
            courseCount: 0
        },
        {
            day: 11,
            courseCount: 0
        },
        {
            day: 12,
            courseCount: 0
        },
        {
            day: 13,
            courseCount: 0
        },
        {
            day: 14,
            courseCount: "2节课"
        },
        {
            day: 15,
            courseCount: "1节课"
        },
        {
            day: 16,
            courseCount: 0
        },
        {
            day: 17,
            courseCount: "4节课"
        },
        {
            day: 18,
            courseCount: 0
        },
        {
            day: 19,
            courseCount: 0
        },
        {
            day: 20,
            courseCount: "14节课"
        },
        {
            day: 21,
            courseCount: "1节课"
        },
        {
            day: 22,
            courseCount: 0
        },
        {
            day: 23,
            courseCount: 0
        },
        {
            day: 24,
            courseCount: 0
        },
        {
            day: 25,
            courseCount: 0
        },
        {
            day: 26,
            courseCount: 0
        },
        {
            day: 27,
            courseCount: 0
        },
        {
            day: 28,
            courseCount: 0
        },
        {
            day: 29,
            courseCount: 0
        },
        {
            day: 30,
            courseCount: 0
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
class courseTable_json extends api {

    function run() {
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002', '用户ID不能为空');
        if(!isset($this->options['year'])   || empty($this->options['year']))   return apis::apiCallback('1002', '年份不能为空');
        if(!isset($this->options['month'])  || empty($this->options['month']))  return apis::apiCallback('1002', '月份不能为空');
        
        $userId   = $this->options['userId']  + 0;
        $year     = $this->options['year'] + 0;
        $month    = $this->options['month'] + 0;
        
        $db = new MySql();
        $month = $month >= 10 ? $month : '0' . $month;
        $day = date("t",strtotime("$year-$month"));
        $dateFormat = $year . '-' . $month . '-01';
        if(!$this->checkDateIsValid($dateFormat, array('Y-m-d'))){
            return apis::apiCallback('1002', '日期参数不正确');
        };
        
		$beginDate = $year . '-' . $month . '-01 06:00:00';
		$endDate   = $year . '-' . $month . "-{$day} 23:59:00";
		
		$result = array();
		$count = array(0);
		$sql = 'select cta_startTime from tang_class_table 
                   LEFT JOIN tang_class on cta_classId=cl_id
                where cta_startTime BETWEEN "' . $beginDate . '" and 
                "'. $endDate . '" and cta_teacherId="' . $userId . '" and cl_status=1';
				
		$result = $db->getAll($sql);
		
		if($result){
			foreach($result as $key=>$val){
				$d = date('d', strtotime($val['cta_startTime']));
				$count[$d] ++;
			}
		}
		
		for($i=1;$i<=$day;$i++){
			$data[$i - 1]['day'] = $i;
			$data[$i - 1]['courseCount'] = array_key_exists($i, $count) ? $count[$i] . '节课' : 0;
		}
		
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
