<?php
/**
 * @api                    {post} /api/income.json 教师月收益
 * @apiDescription         教师月收益
 * @apiName                income_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   @apiParam {string}     userId     教师ID 
   @apiParam {int}        year       年
   @apiParam {int}        month      月
   @apiParam {int}        page       分页
   @apiParam {int}        pageSize   分页大小（10）
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        courseTotal: 21,  //授课次数
        totalIncome: 2100,   //授课总收益
        incomeList: [
            {
                id:"2016112314570645482963140",     //收益ID
                createTime: "2016-11-14 16:50:00",  //收益结算时间
                total: "100.00"                     //收益,
                type: '1'                           //收益类型
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
class income_json extends api {

    function run() {
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002', '用户ID不能为空');
        if(!isset($this->options['year'])   || empty($this->options['year']))   return apis::apiCallback('1002', '年份不能为空');
        if(!isset($this->options['month'])  || empty($this->options['month']))  return apis::apiCallback('1002', '月份不能为空');
        
        $userId   = $this->options['userId'] + 0;
        $year     = $this->options['year']   + 0;
        $month    = $this->options['month']  + 0;
        $page     = isset($this->options['page']) ? $this->options['page'] + 0 : 1;
        $pageSize = isset($this->options['pageSize']) ? $this->options['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        $db = new MySql();
        $data = array('courseTotal' => 0, 'totalIncome' => 0, 'incomeList' => array());
        
        $month = $month >= 10 ? $month : '0' . $month;
        $date = $year . '-' . $month . '-01';
        
        if(!$this->checkDateIsValid($date, array('Y-m-d'))){
            return apis::apiCallback('1002', '日期参数不正确');
        };
        
        $day = date("t",strtotime("$year-$month"));
        $date .= ' 00:00:00';
        $monthLastDate = $year . '-' . $month . '-' . $day . ' 23:59:59';
        $sql = 'select tti_id as id,tti_createTime as createTime,tti_total as total,1 as type from tang_teacher_income 
            where tti_userId="' . $userId . '" and tti_createTime>="' . $date . '" and tti_createTime<="' . $monthLastDate . '" limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        
        $incomeList = $db->getAll($sql);
        
        $data['courseTotal'] = 0;
        $data['incomeList']  = array();
        
        if($incomeList){
            $data['courseTotal'] = count($incomeList);
            foreach($incomeList as $key=>$val){
                $data['totalIncome'] += $val['total'];
            }
            $data['incomeList'] = $incomeList;
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
