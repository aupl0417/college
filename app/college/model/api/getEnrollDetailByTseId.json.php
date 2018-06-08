<?php
 /**
 * @api                    {get} /api/getEnrollDetailByTseId.json 报名详情
 * @apiDescription         获取课程报名详情
 * @apiName                getEnrollDetailByTseId.json
 * @apiGroup               Class
 * @apiPermission          aupl
 *
 
   @apiParam {string}     tseId  课程订单号
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
     "code": "1001",
     "data": {
         "tseId": "2016111817345660025631106",
         "tsePid": "",
         "tseUserId": "300",
         "tseUserTrueName": "美眉",
         "tseCertNum": "",
         "tseClassId": "136",
         "tseOrderId": "2016111817345667754434043",
         "tseFee": "100.00",
         "tsePayFee": "0.00",
         "tseStatus": "1",
         "tseState": "0",
         "tseReason": null,
         "tseEId": null,
         "tseETime": null,
         "tseCheckInTime": null,
         "tsePayTime": "2016-11-18 17:34:56",
         "tseCreateTime": "2016-11-18 17:34:56",
         "tseTeam": "0",
         "tseArrivalTime": null,
         "tseStation": null,
         "tseCounts": null,
         "clId": "136",
         "clGradeId": "0",
         "clName": "test班级（16）",
         "clLogo": "http://192.168.3.201:80/v1/tfs/T1zyETBCLT1RCvBVdK.png",
         "clEnrollStartTime": "2016-11-18",
         "clEnrollEndTime": "2016-11-19",
         "clStartTime": "2016-11-20",
         "clEndTime": "2016-11-23",
         "clHeadmasterId": "328",
         "clTeachingType": "0",
         "clHostelMemo": "包住",
         "clHostel": "2",
         "clCateringMemo": "包吃",
         "clCatering": "2",
         "clCost": "100.00",
         "clPayFee": "0.00",
         "clCodeImage": null,
         "tangCollege": "4",
         "clDefaultTrainingsiteId": "0",
         "clAllowableNumber": "10",
         "clDescription": "<p>天天天天天天天天</p>",
         "clCreateTime": "2016-11-18 09:09:40",
         "clState": "0",
         "clStatus": "1",
         "clCondition": "1",
         "clReason": "同意",
         "clIsHot": "0",
         "clUpdateTime": null,
         "clTeamNum": "2",
         "clTeamStudentNum": "5",
         "clNumber": "26",
         "brId": "4",
         "brAreaId": "4550",
         "brParentId": "1",
         "brAddress": "",
         "brName": "邵阳分院",
         "brLevel": "3",
         "brState": "1",
         "brReason": "顶替枯",
         "brCreateTime": "2016-08-18 00:00:00",
         "brUpdateTime": "2016-08-27 15:31:22",
         "enrollCount": "2"
     }
     }
 *
 * @apiErrorExample        Error-Response:
     {
     "code": "1002",
     "data": "没有获取到数据!"
     }
 *
 */
class getEnrollDetailByTseId_json extends api{

    private $db;

    public function run(){
        $options =$this->options;
        $this->db = new MySql();
        if(!isset($options['tseId']) || empty($options['tseId'])) return apis::apiCallback('1002','报名Id为空');

        $sql="SELECT *,(SELECT count(tse_id) FROM tang_student_enroll WHERE tse_classId =tse.tse_classId and tse_state !=-1 and tse_status !=-1) enrollCount FROM tang_student_enroll tse LEFT JOIN tang_class tc ON tse.tse_classId = tc.cl_id LEFT JOIN tang_branch tb ON tangCollege =tb.br_id WHERE tse_id ='{$options['tseId']}'";

        $data= $this->db->getRow($sql);
        if ($data){
            $data['cl_logo'] =TFS_APIURL.'/'.$data['cl_logo'];
            $data =self::convertkey($data);
        }

        if(!$data){
            return apis::apiCallback('1002', '没有获取到数据!');
        }

        return apis::apiCallback('1001', $data);
    }

    //转义字段名称
    private function convertkey($data){
        $newData =array();
        if (is_array($data)){
            foreach ($data as $key =>$value){
                if (strpos($key,'_')!==false){
                    $temp =explode('_',$key,2);
                    $keyname =$temp[0].ucfirst($temp[1]);
                }else{
                    $keyname = $key;
                }
                $newData[$keyname] =$data[$key];
            }
            return $newData;
        }else{
            return $data;
        }

    }

}