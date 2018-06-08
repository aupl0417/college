<?php
/* 获取学员信息接口
 * @param $studentId   type : int 学员userid must
 * @author lirong
 * */

/**
 * @api                    {post} /api/getClassStudentList.json 获取学员参加过的班级记录
 * @apiDescription         获取学员参加过的班级记录
 * @apiName                getClassStudentList.json
 * @apiGroup               学员
 * @apiPermission          aupl
 *
 * @apiParam {string}     studentId      用户id
 *
 * @apiSuccessExample      Success-Response:
 *
    {
    "code": "1001",
    "data": [
    {
    "id": "184",
    "studentId": "283",
    "score": "0",
    "classID": "28",
    "className": "大唐天下C+商业模式培训会（第5期）",
    "brName": "长沙分院",
    "classState": "2",
    "classLogo": "T12cYTB5xT1RCvBVdK.png",
    "EnrollNum": "1"
    },
    {
    "id": "182",
    "studentId": "283",
    "score": "0",
    "classID": "27",
    "className": "大唐天下C+商业模式培训会（第4期）",
    "brName": "长沙分院",
    "classState": "2",
    "classLogo": "T1A2hTB4LT1RCvBVdK.png",
    "EnrollNum": "0"
    }
    ]
    }
 *
 */

class getClassStudentList_json extends api {

    private $db;
    public function run(){
        $options =$this->options;
        $this->db = new MySql();
        if(!isset($options['studentId']) || empty($options['studentId'])) return apis::apiCallback('1002','学员Id为空');

        $studentId =$options['studentId'];

        $sql="SELECT cs_id as id,cs_studentId as studentId,cs_score as score,cl_id as classID, cl_name as className, br_name as brName,cl_state as classState,cl_logo as classLogo, (SELECT COUNT(tse_id) FROM tang_student_enroll WHERE tse_classId = tcs.cs_classId AND tse_state = 1 AND tse_status = 1 ) EnrollNum FROM tang_class_student tcs LEFT JOIN tang_class tc ON tcs.cs_classId = tc.cl_id LEFT JOIN tang_branch tb ON tc.tangCollege = tb.br_id WHERE cs_studentId = $studentId ORDER BY cs_createTime DESC";
        $data = $this->db->getAll($sql);
        if(!$data){
            return apis::apiCallback('1002', '没有信息!');
        }

        foreach ($data as &$item){
            $item['classLogo'] =TFS_APIURL.'/'.$item['classLogo'];
        }

        return apis::apiCallback('1001', $data);
    }

}