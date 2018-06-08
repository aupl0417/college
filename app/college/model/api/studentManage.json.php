<?php
/**
 * @api                    {post} /api/studentManage 教师课程列表
 * @apiDescription         教师课程列表
 * @apiName                studentManage_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   @apiParam {int}        classId   班级ID 
   @apiParam {int}        page      分页id
   @apiParam {int}        pageSize  分页大小
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            id: "321",              //用户Id
            username: 'atestnum210', //用户名
            userId: 'd939b215b61a4cbf7afa988049d877f7',  //userId
            avatar: 'http://192.168.3.201:80/v1/tfs/T1uyhTBCDT1RCvBVdK.jpg',
            trueName: "test二一零",   //用户真实姓名
            mobile: "14412124545"   //手机号
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
class studentManage_json extends api {

    function run() {
//         if(!isset($this->options['userId'])  || empty($this->options['userId']))  return apis::apiCallback('1002', '用户ID不能为空');
        if(!isset($this->options['classId']) || empty($this->options['classId'])) return apis::apiCallback('1002', '班级ID不能为空');
        
//         $userId   = $this->options['userId']  + 0;
        $classId  = $this->options['classId'] + 0;
        $page     = isset($this->options['page']) ? $this->options['page'] + 0 : 1;
        $pageSize = isset($this->options['pageSize']) ? $this->options['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        $db = new MySql();
        $field = 'id,username,trueName,avatar,userId,mobile';
        $sql = 'select ' . $field . ' from tang_class_student 
                LEFT JOIN tang_ucenter_member on cs_studentId=id 
            where cs_classId="' . $classId . '" limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        $studentList = $db->getAll($sql);
        if(!$studentList){
            return apis::apiCallback('1002', '该班级暂无学员');
        }
        
        apis::apiCallback('1001', $studentList);
    }
}
