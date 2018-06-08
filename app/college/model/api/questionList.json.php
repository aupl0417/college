<?php
/**
 * @api                    {post} /api/questionList.json 提问列表
 * @apiDescription         提问列表
 * @apiName                questionList_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   
   @apiParam {int}        userType   用户类型（0：学员；1：讲师） 
   @apiParam {string}     userId     用户ID
   @apiParam {int}        classId    班级ID（可选：用于班级答疑时列表）
   @apiParam {int}        userType   用户类型（0：学员；1：讲师）
   @apiParam {int}        page       分页
   @apiParam {int}        pageSize   分页大小
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            id: "11",  //提问ID
            title: "老师，课件有错误",   //提问标题
            classId: "139",         //班级ID
            courseId: "63",         //课程ID
            courseName: "大唐天下C+商业模式说明会",   //课程名
            createTime: "2016-10-15 16:07:12", //提问时间
            replyCount: "1",                   //说话总次数
            lastReply: "尽职尽责",                //最后一次回复内容
            username: "atestnum201"            //最后一次回复的用户名
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
class questionList_json extends api {

    function run() {
        $userType = isset($this->options['userType']) ? $this->options['userType'] + 0 : 1;
        $page     = isset($this->options['page']) ? $this->options['page'] + 0 : 1;
        $pageSize = isset($this->options['pageSize']) ? $this->options['pageSize'] + 0 : 10;
        if($pageSize == 0 || $page == 0){
            return apis::apiCallback('1002', '分页参数非法');
        }
        
        $db = new MySql();
        
        $field = 'tsi_id as id,tsi_title as title,tsi_classId as classId,tsi_courseId as courseId,tsi_userId as userId,tsi_teacherId as teacherId,co_name as courseName,tsi_createTime as createTime,(select count(tsi_id) from tang_teacher_student_interaction where tsi_pid=id and tsi_status=0) as replyCount';
        $sql = 'select ' . $field . ' from tang_teacher_student_interaction 
                LEFT JOIN tang_course on co_id=tsi_courseId 
                where tsi_pid=0 and tsi_status=0 ';
        
        $userId   = $this->options['userId'] + 0;
        if($userType == 1){
            $fieldName = 'tsi_teacherId';
        }else {
            $fieldName = 'tsi_userId';
        }
        $sql .= 'and ' . $fieldName . '="' . $userId . '" ';
        
        if(isset($this->options['classId']) && !empty($this->options['classId'])){
            $sql .= 'and tsi_classId="' . intval($this->options['classId']) . '" ';
        }
        $sql .= ' order by tsi_createTime desc ';
        $limit = 'limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        $sql .= $limit;
        
        $result = $db->getAll($sql);

        if(!$result){
            return apis::apiCallback('1002', '暂无提问');
        }

        foreach($result as $key=>&$val){
            $replySql = 'select tsi_content,u.username as student,t.username as teacher from tang_teacher_student_interaction 
                LEFT JOIN tang_ucenter_member u on %s=u.id 
                LEFT JOIN tang_ucenter_member t on %s=t.id 
                where tsi_pid="%s" order by tsi_id desc limit 1';
            
            $replySql = sprintf($replySql, 'tsi_userId', 'tsi_teacherId', $val['id']);
            
            $reply = $db->getRow($replySql);
            $val['lastReply'] = $reply ? $reply['tsi_content'] : '';
            $val['username']   = $reply ? ($reply['student'] ? $reply['student'] : $reply['teacher']) : '';
        }
        
        return apis::apiCallback('1001', $result);
    }
}
