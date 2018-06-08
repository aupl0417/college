<?php
/* 判断用户是否报名某课程
 * @param $userId    type : int    用户id  must
 * @param $classId type : int    班级id   must
 * @author lirong
 * */

/**
 * @api                    {post} /api/existEnroll.json 报名业务接口
 * @apiDescription         判断用户是否报名某课程
 * @apiName                existEnroll.json
 * @apiGroup               学员
 * @apiPermission          adadsa
 *
 *
 *
@apiParam {int}     userId       用户id （userId,userName,mobile三选一）
@apiParam {int}     $classId     用户名     （userId,userName,mobile三选一）

 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
        "code": "1001",
        "data": "用户已报名!"
    }
 *
 */
class existEnroll_json extends api {

    private $db;

    public function run(){

        $this->db=new MySql();

        //验证参数是否存在
        if(!isset($this->options['classId']) || empty($this->options['classId'])) return apis::apiCallback('1002','班级id为空');
        if(!isset($this->options['userId']) || empty($this->options['userId'])) return apis::apiCallback('1002','用户ID为空');

        $classId =$this->options['classId'];
        $userId =$this->options['userId'];

        $count = $this->db->getField('select count(tse_id) from tang_student_enroll where tse_classId="' . $classId . '" and tse_userId="' . $userId . '" and tse_state in (0,1) and tse_status in (0,1,2)');
        if($count){
            return apis::apiCallback('1001','用户已报名!');
        }else{
            return apis::apiCallback('1002','未报名!');
        }
    }

}