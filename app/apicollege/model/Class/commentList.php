<?php
/**
 * @api                    {post} /Class/commentList 评论列表
 * @apiDescription         某个班级或某个学员的评论列表
 * @apiName                commentList
 * @apiGroup               Class
 * @apiPermission          aupl
 *
   @apiParam {int}        appId     设备类型
   @apiParam {string}     deviceID  设备id
   @apiParam {string}     signValue 签名串
   @apiParam {string}     userId    用户ID（用户ID跟班级ID二选一，传用户ID为用户所有评论）
   @apiParam {int}        classId   班级ID（用户ID跟班级ID二选一，传班级ID为班级所有评论）
   @apiParam {int}        type      评论类型（0：所有评论；1：好评；2：中评；3：差评）
   @apiParam {int}        page      分页（可选）
   @apiParam {int}        pageSize  分页大小（可选）
 *
 *
 * @apiSuccessExample      Success-Response: 
 *{    //班级评论列表
	code: 1001,
	msg: "操作成功",
	data: {
		badCount: 0,
		midCount: 0,
		goodCount: 1,
		goodRate: "100%",
		commentList: [
			{
				id: "18",
				userId: "179",
				content: "鼎折覆餗村压力",
				level: "5",
				createTime: "2016-10-23 14:55:25",
				uname: "testnum211",
				avatar: "https://image.dttx.com/v1/tfs/T1TUDTByWT1RCvBVdK.jpg"
			}
		]
	}
  }
 * @apiSuccessExample      Success-Response:
 *{   //我的评论
    code: 1001,
    msg: "操作成功",
    data: {
        badCount: "0",
        midCount: "1",
        goodCount: "2",
        goodRate: "66.67%",
        commentList: [
            {
                id: "79",
                userId: "306",
                content: "67890789",
                level: "5",
                createTime: "2016-11-25 16:56:02",
                className: "test班级(4)",
                classLogo: "http://192.168.3.201:80/v1/tfs/T1QRCTBX_T1RCvBVdK.jpg"
            },
            {
                id: "77",
                userId: "306",
                content: "cgvvbbcccvnm",
                level: "4",
                createTime: "2016-11-14 14:26:46",
                className: "大唐天下C+ 商业模式培训会（第1期）",
                classLogo: "http://192.168.3.201:80/v1/tfs/T1o7xTBXAT1RCvBVdK.png"
            },
            {
                id: "76",
                userId: "306",
                content: "vjnhgggbmkmmn",
                level: "5",
                createTime: "2016-11-14 14:26:29",
                className: "大唐天下C+ 商业模式培训会（第1期）",
                classLogo: "http://192.168.3.201:80/v1/tfs/T1o7xTBXAT1RCvBVdK.png"
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
class commentList extends newBaseApi{
	
    function __construct($options) {
        parent::__construct($options);
        $this->nowDate = date('Y-m-d');
    }
	
    function run() {
        
        $type     = isset($this->data['type']) ? $this->data['type'] + 0 : 0;
        $page     = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 0;
        $pageSize = isset($this->data['pageSize']) && !empty($this->data['pageSize']) ? $this->data['pageSize'] + 0 : 0;
        
        $classId = 0;
        $where =  'tc_classTableId=0 ';
        if(isset($this->data['classId']) && !empty($this->data['classId'])){
            $field = 'tc_id as id,tc_userId as userId,tc_content as content,tc_level as level,tc_createTime as createTime,tc_type as type,username as uname,avatar';
            $classId = $this->data['classId'] + 0;
            $where .= 'and tc_classId="' . $classId . '"';
        }else if(isset($this->data['userId']) && !empty($this->data['userId'])){
            $field = 'tc_id as id,tc_userId as userId,tc_content as content,tc_level as level,tc_createTime as createTime,tc_type as type,cl_name as className,cl_logo as classLogo';
            $where .= 'and tc_userId="' . $this->uid . '"';
        }
        
        $condition = '';
        switch ($type) {
            case 1:
                $condition = 'and tc_level=5';
                break;
            case 2:
                $condition = 'and tc_level>=3 and tc_level<=4';
                break;
            case 3:
                $condition = 'and tc_level>=0 and tc_level<3';
                break;
            default:
                $condition = '';
                break;
        }
        
        $condition = $where . $condition;
        
        $sql = 'select ' . $field . ' from tang_teacher_comment 
               %s 
               where ' . $condition . ' order by tc_createTime desc';
        
        if(isset($this->data['classId']) && !empty($this->data['classId'])){
            $sql = sprintf($sql, 'LEFT JOIN tang_ucenter_member on tc_userId=id');
        }else if(isset($this->data['userId']) && !empty($this->data['userId'])){
            $sql = sprintf($sql, 'LEFT JOIN tang_class on tc_classId=cl_id');
        }
        
        if($page && $pageSize){
            $sql .= ' limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        }
        
        $commentList = $this->db->getAll($sql);

        !$commentList && $this->apiReturn(1002, '暂无评论');

        foreach ($commentList as $key=>&$val){
            if($classId){
                $val['avatar'] = $val['avatar'] ? TFS_APIURL . '/' . $val['avatar'] : 'https://image.dttx.com/v1/tfs/T1TUDTByWT1RCvBVdK.jpg';
                if($val['type'] == 1){
                    $val['uname'] = '匿名用户';
                }
            }else if($this->uid){
                $val['classLogo'] = $val['classLogo'] ? TFS_APIURL . '/' . $val['classLogo'] : '';
            }
            unset($val['type']);
        }
        $sql = 'select count(tc_id) as count from tang_teacher_comment where ' . $where;
        $data = array(
            'badCount'  => $this->db->getField($sql . ' and tc_level>=0 and tc_level<3'), 
            'midCount'  => $this->db->getField($sql . ' and tc_level>=3 and tc_level<=4'), 
            'goodCount' => $this->db->getField($sql . ' and tc_level=5'), 
            'goodRate'=> '0.0%'
        );
        
        $totalCount = $data['badCount'] + $data['midCount'] + $data['goodCount'];
        if($totalCount > 0){
            $data['goodRate'] = round($data['goodCount'] * 100 / $totalCount, 2) . '%';
        }
        
        $data['commentList'] = $commentList;
        
        $this->apiReturn(1001, '', $data);
    }
    
}
