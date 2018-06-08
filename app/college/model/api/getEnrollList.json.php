<?php
/* 获取个人报名课程列表
 * @param userId   type : int 用户id   must
 * @author lirong
 * */

/**
 * @api                    {post} /api/getEnrollList.json 获取用户报名列表
 * @apiDescription         获取用户报名列表
 * @apiName                getEnrollList.json
 * @apiGroup               学员
 * @apiPermission          adadsa
 *

@apiParam {int}     userId       唐人大学用户id
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
        "code": "1001",
        "data": [
            {
            "tseID": "2016111013290609478210587",
            "collegeName": "长沙分院",
            "className": "大唐天下Ｃ＋商业模式培训会（第8期）",
            "classLogo": "https://image.dttx.com/v1/tfs/T1x6YTBCYT1RCvBVdK.png",
            "classId": "107",
            "count": "8",
            "tseOrderState": "0",
            "tseAuditStatus": "-1",
            "classState": "1",
            "classAuditStatus": "1"
            },
        ]
        }
 *
 */

class getEnrollList_json extends api{

    private $db;

    public function run(){
        $options =$this->options;
        $this->db = new MySql();
        if(!isset($options['userId']) || empty($options['userId'])) return apis::apiCallback('1002','用户userId为空');

        $userId =$options['userId'];
        $sql="SELECT tse_id as tseID, br_name AS collegeName, cl_name AS className,cl_logo as classLogo, cl_id AS classId,cl_startTime as classStartTime, (SELECT COUNT(cl_id) FROM tang_student_enroll WHERE tse_classId = temp.cl_id and tse_state !=-1 AND tse_status !=-1 ) count, tse_status as tseOrderState ,tse_state as tseAuditStatus,tse_fee as tseMoney ,cl_state as classState ,cl_status as classAuditStatus FROM (SELECT br_name, cl_name,cl_logo, cl_id,cl_state,cl_status, cl_startTime FROM tang_branch AS tbr LEFT JOIN tang_class tcl ON tbr.br_id = tcl.tangCollege ) temp LEFT JOIN tang_student_enroll tse ON tse.tse_classId = temp.cl_id WHERE tse.tse_userId = $userId AND tse.tse_status >-1 ORDER BY tse.tse_createTime DESC";

        $result =$this->db->getAll($sql);
        if ($result){
            foreach ($result as &$v){
                $v['classLogo']   = TFS_APIURL.'/'.$v['classLogo'];
            }
        }else{
            return apis::apiCallback('1002', '没有信息!');
        }

        return apis::apiCallback('1001', $result);
    }


}