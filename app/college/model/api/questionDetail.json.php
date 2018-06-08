<?php
/**
 * @api                    {post} /api/questionDetail.json 提问详情
 * @apiDescription         提问详情
 * @apiName                questionDetail_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   
   @apiParam {int}        id         提问ID 
   @apiParam {int}        page       分页
   @apiParam {int}        pageSize   分页大小
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        id: "11",
        title: "老师，课件有错误",
        classId: "139",
        courseId: "63",
        content: "老师，最后一页PPT里面打错了一个字。",
        username: "atestnum201",
        courseName: "大唐天下C+商业模式说明会",
        createTime: "2016-10-15 16:07:12",
        replyCount: 1,
        lists: [
            {
                id: "12",
                avatar: "http://192.168.3.201:80/v1/tfs/T1ktETB4ET1RCvBVdK.jpg",
                content: "尽职尽责",
                username: "atestnum201",
                userType: "0",
                createTime: "2016-10-15 19:03:09"
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
class questionDetail_json extends api {

    function run() {
        if(!isset($this->options['id'])  || empty($this->options['id']))  return apis::apiCallback('1002', '提问ID不能为空');
        
        $id       = $this->options['id'] + 0;
        $page     = isset($this->options['page']) ? $this->options['page'] + 0 : 1;
        $pageSize = isset($this->options['pageSize']) ? $this->options['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        $db = new MySql();
        $field = 'tsi_id as id,tsi_title as title,tsi_classId as classId,tsi_courseId as courseId,tsi_content as content,username,co_name as courseName,tsi_createTime as createTime';
        $sql = 'select %s from tang_teacher_student_interaction 
                 LEFT JOIN tang_course on co_id=tsi_courseId %s
            where tsi_status=0';
        //
        $qsql = sprintf($sql, $field, 'LEFT JOIN tang_ucenter_member on tsi_userId=id');
        $where = ' and tsi_id="' . $id . '" and tsi_pid=0';
        $question = $db->getRow($qsql . $where);
        
        $fields = 'tsi_id as id,tsi_content as content,tsi_userId,tsi_teacherId,tsi_createTime as createTime';
        $map = ' and tsi_pid="' . $id . '"';
        $sql = sprintf($sql, $fields, '');
        $limit = 'limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        $sql .= $map . $limit;
        
        $result = $db->getAll($sql);
        if(!$result){
            $question['replyCount'] = 0;
            $question['lists']      = array();
        }else {
            foreach($result as $key=>&$val){
                if($val['tsi_userId'] == 0){
                    $fieldName = 'tsi_teacherId';
                }else {
                    $fieldName = 'tsi_userId';
                }
                $user = $db->getRow('select avatar,username,identityType from tang_ucenter_member where id="' . $val[$fieldName] . '"');
                $val['username'] = $user ? $user['username'] : '';
                $val['avatar']   = $user ? $user['avatar'] : '';
                $val['userType'] = $user ? $user['identityType'] : '';
                unset($val['tsi_teacherId']);
                unset($val['tsi_userId']);
            }
            
            $question['replyCount'] = count($result);
            $question['lists']      = $result;
        }
        
        return apis::apiCallback('1001', $question);
    }
}
