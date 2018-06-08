<?php
 /**
 * @api                    {post} /Class/search 搜索
 * @apiDescription         搜索课程
 * @apiName                search
 * @apiGroup               Class
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId     设备类型
   @apiParam {string}     deviceID  设备id
   @apiParam {string}     signValue 签名串
   @apiParam {string}     keyword   关键字
 *
 *
 * @apiSuccessExample      Success-Response: 
 *  IOS返回 : 
 *	{
		code: 1001,
		msg: "操作成功",
		data: [
            {
                id: "2",
                name: "201610基础课程",
                allowNumber: "500",
                enrollCount: "3",
                enrollInfo: '3/500报名',
                type: "1"   //1：表示班级
            },
            {
                id: "32",
                name: "线性代数20",
                type: "0"     //0：表示课程
            }
        ]
	}
	
	ANDROID返回 : 
	{
        code: 1001,
        msg: "操作成功",
        data: {
            courseList: [
                {
                    id: "32",
                    name: "线性代数20",
                    type: "0"
                }
            ],
            classList: [
                {
                    id: "2",
                    name: "201610基础课程",
                    allowNumber: "500",
                    enrollCount: "4",
                    type: "1"
                }
            ]
        }
    }
 *
 */
class search extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        (!isset($this->data['keyword']) || empty($this->data['keyword'])) && $this->apiReturn(1004, '关键字不能为空');
		
        $keyword = $this->data['keyword'];
        $field = 'co_id as id, co_name as name,0 as type';
        $sql = 'select ' . $field . ' from tang_course 
               where co_name like "%' . $keyword . '%" order by co_id desc';
        
        $courseList = $this->db->getAll($sql);
        
        $sql = 'select cl_id as id,cl_name as name,cl_allowableNumber as allowNumber,cl_enrolledCount as enrolledCount,(select count(tse_id) from tang_student_enroll where tse_classId=id and tse_state<>-1 and tse_status<>-1) as enrollCount,1 as type from tang_class
                where cl_name like "%' . $keyword . '%" and cl_status=1 and cl_state<>-1 order by cl_id desc';
        
		$classList = array();
        $classList = $this->db->getAll($sql);

		if($classList){
			foreach($classList as &$val){
				$val['enrollInfo'] = $val['enrollCount'] + $val['enrolledCount'] > $val['allowNumber'] ? '已满员' : ($val['enrollCount'] + $val['enrolledCount']) . '/' . $val['allowNumber'] . '报名';
				unset($val['enrolledCount']);
			}
		}
        
        if($this->data['appId'] == 1){
            $data = array_merge($classList, $courseList);
            shuffle($data);//打乱顺序
            
            !$data && $this->apiReturn(1002, '暂无结果');
        }else {
            $data = array(
                'courseList' => $courseList,
                'classList'  => $classList
            );
        }
        
        $this->apiReturn(1001, '', $data);
    }
    
}
