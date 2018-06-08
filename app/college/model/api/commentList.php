<?php
/**
 * @api                    {post} /api/commentList 班级评论列表
 * @apiDescription         某个班级的评论列表
 * @apiName                commentList
 * @apiGroup               Class
 * @apiPermission          aupl
 *

   @apiParam {int}        classId   班级id
   @apiParam {int}        type      评论类型（0：所有评论；1：好评；2：中评；3：差评）
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    "code": 1001,
    "data": {
        "badCount": "0",
        "midCount": "0",
        "goodCount": "40",
        "goodRate": "100%",
        "commentList": [
        {
        "id": "75",
        "userId": "294",
        "content": ".,?!hhhhhjj",
        "level": "5",
        "createTime": "2016-11-12 15:39:40",
        "uname": "匿名用户",
        "avatar": "https://image.dttx.com/v1/tfs/T1TUDTByWT1RCvBVdK.jpg"
        },
        {
        "id": "74",
        "userId": "294",
        "content": ".ghhhhhhhh",
        "level": "5",
        "createTime": "2016-11-12 15:39:13",
        "uname": "匿名用户",
        "avatar": "https://image.dttx.com/v1/tfs/T1TUDTByWT1RCvBVdK.jpg"
        },
    ]
    }
}
 *
 * @apiErrorExample        Error-Response:
     *{
        "code": 1001,
        "data": {
        "badCount": "0",
        "midCount": "0",
        "goodCount": "0",
        "goodRate": "0.0%",
        "commentList": []
        }
    }
 *
 */
class commentList extends api {

    function __construct($options) {
        parent::__construct($options);
        $this->nowDate = date('Y-m-d');
        $this->db= new MySql();
    }
	
    function run() {
        if(!isset($this->options['classId']) || empty($this->options['classId'])) {
            return apis::apiCallback('1002','班级ID不能为空!');
        }
        
        $type = isset($this->options['type']) ? $this->options['type'] + 0 : 0;
        $classId = $this->options['classId'] + 0;
        $where = 'tc_classId="' . $classId . '" and tc_classTableId=0 ';
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
        
        $sql = 'select tc_id as id,tc_userId as userId,tc_content as content,tc_level as level,tc_createTime as createTime,tc_type as type,username as uname,avatar from tang_teacher_comment 
               LEFT JOIN tang_ucenter_member on tc_userId=id 
               where ' . $condition . ' order by tc_createTime desc';


        $commentList = $this->db->getAll($sql);
        
        foreach ($commentList as $key=>&$val){
            if(empty($val['avatar'])){
                $val['avatar'] = 'https://image.dttx.com/v1/tfs/T1TUDTByWT1RCvBVdK.jpg';
            }
            if($val['type'] == 1){
                $val['uname'] = '匿名用户';
            }
            unset($val['type']);
        }
        $sql = 'select count(tc_id) as count from tang_teacher_comment where ' . $where;
        $data = array(
            'badCount'  => $this->db->getField($sql . ' and tc_level>=0 and tc_level<3'), 
            'midCount'  => $this->db->getField($sql . ' and tc_level>=3 and tc_level<=4'), 
            'goodCount' => $this->db->getField($sql . ' and tc_level=5'), 
            'goodRate'=> '0.0%',
            'classId'=>$classId
        );
        
        $totalCount = $data['badCount'] + $data['midCount'] + $data['goodCount'];
        if($totalCount > 0){
            $data['goodRate'] = round($data['goodCount'] * 100 / $totalCount, 2) . '%';
        }
        
        $data['commentList'] = $commentList;
        
        return apis::apiCallback(1001, $data);
    }
    
}
