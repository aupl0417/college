<?php
 /**
 * @api                    {post} /Class/getTopWords 热搜词条
 * @apiDescription         随机获取5条热搜词条
 * @apiName                getTopWords
 * @apiGroup               Class
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId     设备类型
   @apiParam {string}     deviceID  设备id
   @apiParam {string}     signValue 签名串
 *
 *
 * @apiSuccessExample      Success-Response: 
 *	{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            id: "57",
            name: "大唐天下C 商业模式"
        },
        {
            id: "58",
            name: "市场运营"
        },
        {
            id: "59",
            name: "电子商务"
        },
        {
            id: "61",
            name: "C 商业系统模式介绍"
        },
        {
            id: "63",
            name: "大唐天下C+商业模式说明会"
        },
        {
            id: "64",
            name: "联合代理招商会议"
        }
    ]
  }
 *
 */
class getTopWords extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        $field = 'co_name as name';
        $sql = 'SELECT co_id as id,co_name as name FROM `tang_course` WHERE co_id >= (SELECT floor(RAND() * (SELECT MAX(co_id) FROM `tang_course`))) 
               and co_state=1 LIMIT 6';
        
        $courseList = $this->db->getAll($sql);
        !$courseList && $this->apiReturn(1002, '暂无结果');
        
        $this->apiReturn(1001, '', $courseList);
    }
    
}
