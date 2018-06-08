<?php
/* 获取学生和班级信息
 * @param $uname   type : string 用户id   must
 * @param $payType type : int    支付方式      默认 ：0
 * @param $classId type : int    班级id   must
 * @author adadsa
 * */
 
 /**
 * @api                    {post} /api/getStudentInfo.json 获取学员的班级信息
 * @apiDescription         获取学员的班级信息
 * @apiName                getStudentInfo.json
 * @apiGroup               学员
 * @apiPermission          adadsa 
 *

   @apiParam {int}     studentId       用户id
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
     "code": "1001",
     "data": {
         "csid": "187",
         "classId": "2",
         "studentId": "283",
         "score": "0",
         "isGraduated": "0",
         "status": "0",
         "createTime": "2016-11-05 11:02:06"
     }
 }
 *
* @apiErrorExample        Error-Response:
*{
     code: 404,
     data: "用户不存在",
 }
  *
 */
class getStudentInfo_json extends api {

    private $db;

    function run() {
		$this->db = new MySql();
        if(!isset($this->options['studentId']) || empty($this->options['studentId'])) return apis::apiCallback('1002','用户studentId为空');

        $userInfo = array();
        $userInfo = $this->db->getRow('select cs_id as csid,cs_classId as classId,cs_studentId as studentId,cs_score as score,cs_isGraduated as isGraduated,cs_status as status,cs_createTime as createTime from tang_class_student where cs_studentId="' . $this->options['studentId'] . '"');
        if(!$userInfo){
            return apis::apiCallback('1002', '用户不存在');
        }

        return apis::apiCallback('1001', $userInfo);
    }
	
    
}
