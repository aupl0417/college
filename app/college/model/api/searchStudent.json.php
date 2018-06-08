<?php
/**
 * @api                    {post} /api/searchStudent.json 学员搜索
 * @apiDescription         学员搜索
 * @apiName                searchStudent_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   @apiParam {int}        classId   班级ID 
   @apiParam {int}        userId    教师ID
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
class searchStudent_json extends api {

    function run() {
        if(!isset($this->options['userId'])  || empty($this->options['userId']))  return apis::apiCallback('1002', '用户ID不能为空');
        if(!isset($this->options['classId']) || empty($this->options['classId'])) return apis::apiCallback('1002', '班级ID不能为空');
        if(!isset($this->options['keyword']) || empty($this->options['keyword'])) return apis::apiCallback('1002', '学员名称不能为空');
        
        $userId   = $this->options['userId']  + 0;
        $classId  = $this->options['classId'] + 0;
        $keyword  = trim($this->options['keyword']);
        
        $db = new MySql();
        
        $teacherIds = $db->getAll('select distinct(cta_teacherId) from tang_class_table where cta_classId="' . $classId . '"');
        $teacherIds = array_column($teacherIds, 'cta_teacherId');
        
        if(!in_array($userId, $teacherIds)){
            return apis::apiCallback('1002', '您不是该班级教师');
        }
        
        $field = 'id,username,trueName,mobile,userId,avatar';
        $sql = 'select ' . $field . ' from tang_class_student 
                LEFT JOIN tang_ucenter_member on cs_studentId=id 
            where cs_classId="' . $classId . '" and trueName like "%' . $keyword . '%"';
        $searchList = $db->getAll($sql);
        if(!$searchList){
            return apis::apiCallback('1002', '该班级暂无学员');
        }
        
        apis::apiCallback('1001', $searchList);
    }
}
