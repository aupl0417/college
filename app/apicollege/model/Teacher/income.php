<?php
/**
 * @api                    {post} /Teacher/income 讲师月收益
 * @apiDescription         讲师月收益
 * @apiName                income
 * @apiGroup               Teacher
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型 
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
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
                total: "100.00"                     //收益
                type: '1'                           //收益类型(1:授课；0：课件)
            }
        ]
    }
  }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: 404,
    msg: "用户id不能为空",
    data: null
  }
 *
 */
class income extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = date('Y-m-d H:i:s');
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['year'])   || empty($this->data['year']))  && $this->apiReturn(1002, '请输入年份');
        (!isset($this->data['month'])  || empty($this->data['month'])) && $this->apiReturn(1002, '请输入月份');
        
        $uid     = trim($this->data['userId']);
        $year    = $this->data['year']   + 0;
        $month   = $this->data['month']  + 0;
        $page     = isset($this->data['page']) ? $this->data['page'] + 0 : 1;
        $pageSize = isset($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        if(!$this->uid){
            $this->apiReturn(1002, '教师不存在');
        }
        
        if($this->userInfo['identityType'] == 0){
            $this->apiReturn(1002, '您的身份不是讲师！');
        }
        
        $params = array(
            'userId' =>$this->uid,
            'uid'    =>$uid
        );
        
        $res = apis::request('college/api/settleReward.json', $params, true);
        
        if($res['code'] == '1001'){
            $param = array(
                'userId'   => $this->uid,
                'year'     => $year,
                'month'    => $month,
                'page'     => $page,
                'pageSize' => $pageSize
            );
            
            $result = apis::request('college/api/income.json', $param, true);
            
            if($result['code'] != '1001'){
                $this->apiReturn(1002, '', $result['data']);
            }
            
            $this->apiReturn(1001, '', $result['data']);
        }
    }
}
