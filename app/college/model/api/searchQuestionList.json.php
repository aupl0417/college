<?php
/**
 * @api                    {post} /api/searchQuestionList.json 提问搜索列表
 * @apiDescription         提问搜索列表
 * @apiName                searchQuestionList_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   @apiParam {string}     userId     用户ID
   @apiParam {int}        userType   用户类型（0：学员；1：讲师） 
   @apiParam {int}        classId    班级ID（可选：用于班级答疑时列表）
   @apiParam {string}     keyword    搜索关键字
   @apiParam {int}        page       分页
   @apiParam {int}        pageSize   分页大小
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
class searchQuestionList_json extends api {

    function run() {
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002', '用户ID不能为空');
        if(!isset($this->options['keyword']) || empty($this->options['keyword'])) return apis::apiCallback('1002', '请输入提问或课程名');
                        
        $userId   = $this->options['userId'] + 0;
        $keyword  = trim($this->options['keyword']);
        $userType = isset($this->options['userType']) ? $this->options['userType'] + 0 : 1;
        $page     = isset($this->options['page']) ? $this->options['page'] + 0 : 1;
        $pageSize = isset($this->options['pageSize']) ? $this->options['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        $db = new MySql();
        $field = 'tsi_id as id,tsi_title as title,tsi_classId as classId,tsi_courseId as courseId,co_name as courseName,tsi_createTime as createTime,(select count(tsi_id) from tang_teacher_student_interaction where tsi_pid=id) as replyCount';
        $sql = 'select ' . $field . ' from tang_teacher_student_interaction 
                LEFT JOIN tang_course on co_id=tsi_courseId 
                where tsi_pid=0 and tsi_status=0 ';
        
        
        $userId   = $this->options['userId'] + 0;
        if($userType == 1){
            $fieldName = 'tsi_teacherId';
        }else {
            $fieldName = 'tsi_userId';
        }
        $sql .= 'and ' . $fieldName . '="' . $userId . '"';
        
        if(isset($this->options['classId']) && !empty($this->options['classId'])){
            $sql .= 'and tsi_classId="' . intval($this->options['classId']) . '" ';
        }
        $sql .= ' and (tsi_title like "%' . $keyword . '%" or co_name like "%' . $keyword . '%") ';
        
        $limit = 'limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        $sql .= $limit;
        
        $result = $db->getAll($sql);

        if(!$result){
            return apis::apiCallback('1002', '暂无结果');
        }

        foreach($result as $key=>&$val){
            $replySql = 'select tsi_content,username from tang_teacher_student_interaction 
                LEFT JOIN tang_ucenter_member on tsi_userId=id 
                where tsi_pid="' . $val['id'] . '" order by tsi_id desc limit 1';
            $reply = $db->getRow($replySql);
            $val['lastReply'] = $reply ? $reply['tsi_content'] : '';
            $val['username']   = $reply ? $reply['username'] : '';
        }
        
        apis::apiCallback('1001', $result);
    }
}
