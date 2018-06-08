<?php
/* 报名接口
 * @param $uname   type : string 用户id   must
 * @param $payType type : int    支付方式      默认 ：0
 * @param $classId type : int    班级id   must
 * @author aupl
 * */
 
 /**
 * @api                    {post} /Enroll/index.json 报名接口 --新版本
 * @apiDescription         学员报名
 * @apiName                index.json
 * @apiGroup               Enroll
 * @apiPermission          aupl 
 *
 * @apiParam {int}        appId        设备类型
   @apiParam {string}     deviceID     设备id
   @apiParam {string}     signValue    签名串
 * @apiParam {int}        classId      课时id
   @apiParam {string}     userId       用户id
   @apiParam {int}        payType      支付方式（可选，默认为0）
   @apiParam {string}     province     所属区域（必选）
   @apiParam {int}        carService   接车服务（可选，默认为0：否， 1：是）
   @apiParam {int}        arrivalTime  到站时间（有接车服务时为 必选）
   @apiParam {int}        station      到达站点（有接车服务时为 必选）
   @apiParam {int}        counts       接站人数（有接车服务时为 必选）
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
		code: 1001, 
		msg: "报名成功",
		data: null
	}
 *
 */
class index_json extends baseApi{
    
    function __construct($options) { 
        parent::__construct($options);
		$this->nowDate = date('Y-m-d');
    }
	
    function run() {
        //验证参数是否存在
        (!isset($this->data['userId'])  || empty($this->data['userId']))  && $this->apiReturn(404);
        (!isset($this->data['classId']) || empty($this->data['classId'])) && $this->apiReturn(502);
        
        $payType = (isset($this->data['payType']) && !empty($this->data['payType'])) ? $this->data['payType'] + 0 : 0;
        $carService = (isset($this->data['carService']) && !empty($this->data['carService'])) ? $this->data['carService'] + 0 : 0;
        
        if($carService){
            (!isset($this->data['arrivalTime'])  || empty($this->data['arrivalTime']))  && $this->apiReturn(1002, '请填写接站时间');
            (!isset($this->data['station'])      || empty($this->data['station']))      && $this->apiReturn(1002, '请填写接站地点');
            (!isset($this->data['counts'])       || empty($this->data['counts']))       && $this->apiReturn(1002, '请选择接站人数');
        }
        
        $userId      = $this->data['userId'];
        $classId     = $this->data['classId'] + 0;
        $province    = isset($this->data['province'])&& !empty($this->data['province']) ? $this->data['province'] : '';//所属区域（省份）
        $arrivalTime = isset($this->data['arrivalTime']) ? trim($this->data['arrivalTime']) : '';
        
        $params = array(
            'classId'     => $classId,
            'userId'      => $userId,
            'payType'     => $payType,
            'province'    => $province,
            'arrivalTime' => $arrivalTime,
            'station'     => isset($this->data['station']) ? trim($this->data['station']) : '',
            'counts'      => isset($this->data['counts']) ? $this->data['counts'] + 0 : 0
        );

        $result = apis::request('/college/api/enroll.json', $params, true);
        
        (!is_array($result)) && $this->apiReturn(1002, $result);
        
        if($result['code'] != '1001'){
            $this->apiReturn(1002, $result['data']);
        }else {
            $this->apiReturn(1001, '报名成功');
        }
        
    }
    
}
