<?php
/**
 * @api                    {get} /api/classCollectionList.json 课程收藏列表
 * @apiDescription         获取课程收藏列表
 * @apiName                classCollectionList.json
 * @apiGroup               Class
 * @apiPermission          aupl
 *

@apiParam {int}     userId  用户ID
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    "code": "1001",
    "data": [{
        "tccId": "22",
        "tccUserId": "304",
        "tccClassId": "123",
        "tccDeviceId": "ec8468b7fd99c49dd3e71890c62f9dca",
        "tccCreateTime": "2016-11-14 10:54:29",
        "clId": "123",
        "clGradeId": "0",
        "clName": "test班级(4)",
        "clLogo": "http:\/\/192.168.3.201:80\/v1\/tfs\/T1HyETBCLT1RCvBVdK.png",
        "clEnrollStartTime": "2016-11-12",
        "clEnrollEndTime": "2016-11-17",
        "clStartTime": "2016-11-12",
        "clEndTime": "2016-11-14",
        "clHeadmasterId": "23",
        "clTeachingType": "0",
        "clHostelMemo": null,
        "clHostel": "1",
        "clCateringMemo": null,
        "clCatering": "1",
        "clCost": "100.00",
        "clPayFee": "0.00",
        "clCodeImage": null,
        "tangCollege": "2",
        "clDefaultTrainingsiteId": "1",
        "clAllowableNumber": "6",
        "clDescription": "",
        "clState": "2",
        "clStatus": "1",
        "clCondition": "1",
        "clReason": "同意",
        "clIsHot": "0",
        "clUpdateTime": null,
        "clTeamNum": "2",
        "clTeamStudentNum": "3",
        "clNumber": "14",
        "enrollCount": "17"
        }, {
        "tccId": "30",
        "tccUserId": "304",
        "tccClassId": "124",
        "tccDeviceId": "0dba25dda6ee7b655e1bdb686afd1d0b",
        "tccCreateTime": "2016-11-15 10:25:20",
        "clId": "124",
        "clGradeId": "0",
        "clName": "test班级(5)",
        "clLogo": "http:\/\/192.168.3.201:80\/v1\/tfs\/T1HtETBC_T1RCvBVdK.png",
        "clEnrollStartTime": "2016-11-12",
        "clEnrollEndTime": "2016-11-12",
        "clStartTime": "2016-11-12",
        "clEndTime": "2016-11-12",
        "clHeadmasterId": "173",
        "clTeachingType": "0",
        "clHostelMemo": "2",
        "clHostel": "1",
        "clCateringMemo": "1",
        "clCatering": "1",
        "clCost": "100.00",
        "clPayFee": "0.00",
        "clCodeImage": null,
        "tangCollege": "3",
        "clDefaultTrainingsiteId": "1",
        "clAllowableNumber": "2",
        "clDescription": "<p>模板内容<\/p>", "clCreateTime": "2016-11-12 14:12:43",
        "clState": "1",
        "clStatus": "1",
        "clCondition": "1",
        "clReason": "同意",
        "clIsHot": "0",
        "clUpdateTime": null,
        "clTeamNum": "1",
        "clTeamStudentNum": "2",
        "clNumber": "15",
        "enrollCount": "2"
    }]
}
 *
 * @apiErrorExample        Error-Response:
{
"code": "1002",
"data": "没有获取到数据!"
}
 *
 */
class classCollectionList_json extends api {

    public function run(){

        if(!isset($this->options['userId']) || empty(intval($this->options['userId']))){
            return apis::apiCallback('1002','用户ID不能为空!');

        }
        $db =new MySql();
        $userId =intval($this->options['userId']);
        $sql="SELECT *,(SELECT count(tse_id) FROM tang_student_enroll WHERE tse_classId = tcc.tcc_classId ) as enrollCount FROM tang_class_collection tcc LEFT JOIN tang_class tc ON tcc.tcc_classId =tc.cl_id WHERE tcc.tcc_userId ={$userId}";

        $data =$db->getAll($sql);

        if (empty($data)){
            return apis::apiCallback('1002','没有获取到数据!');
        }

        foreach ($data as $k => &$item){
            if (!empty($data[$k]['cl_logo'])){
                $data[$k]['cl_logo'] =TFS_APIURL.'/'.$item['cl_logo'];
            }
            $item =self::convertkey($item);
        }
        return apis::apiCallback('1001',$data);
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