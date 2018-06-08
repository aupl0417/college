<?php
/**
 * @api                    {get} /api/attendRecordList.json 获取签到列表
 * @apiDescription         获取签到记录列表
 * @apiName                attendRecordList.json
 * @apiGroup               Class
 * @apiPermission          lirong
 *
 * @apiParam {int}     userId       用户ID
 * @apiParam {int}     courseId     课程ID （可选）
 * @apiParam {int}     state  （study）      类型（签到/学习记录） （可选）
 *
 *
 * @apiSuccessExample      Success-Response:
 * {
    "code": "1001",
    "data": [
        {
        "attId": "261",
        "attUserId": "304",
        "attClassTableId": "212",
        "attBranchId": "2",
        "attCreateTime": "2016-11-14 13:49:52",
        "ctaId": "212",
        "ctaSort": "0",
        "ctaDescription": "",
        "ctaClassId": "123",
        "ctaImage": null,
        "ctaTeacherId": "305",
        "ctaCourseId": "67",
        "ctaTrainingsiteId": "1",
        "ctaStartTime": "2016-11-14 16:30:00",
        "ctaEndTime": "2016-11-14 16:50:00",
        "ctaCreateTime": "2016-11-12 10:03:30",
        "coName": "test课程（2）",
        "coLogo": "http://192.168.3.201:80/v1/tfs/T1AaCTBXAT1RCvBVdK.jpg",
        "traName": "308会议室",
        "attType": "sigin"
        }
    ]
}
 *
 * @apiErrorExample        Error-Response:
    {
    "code": "1002",
    "data": "暂无学习记录数据!"
    }
 *
 */
class attendRecordList_json extends api{

    public function run(){

        (!isset($this->options['userId']) || empty(intval($this->options['userId']))) && apis::apiCallback('1002','用户ID不能为空!');
        $db =new MySql();
        $userId=intval($this->options['userId']);
        $sql ="SELECT ta.*,cl_name,cl_logo,cl_enrollStartTime,cl_enrollEndTime,tangCollege,cl_allowableNumber,cl_state,(SELECT COUNT(*) FROM tang_student_enroll tse WHERE tse_classId =ta.att_classId AND tse_state!=-1 and tse_status!=-1)as enrollCount FROM `tang_attendance`as ta LEFT JOIN tang_class tc ON ta.att_classId =tc.cl_id WHERE att_userId ={$userId}  GROUP BY att_classId ORDER BY att_id DESC";

        $data =$db->getAll($sql);

        if (empty($data)){
            return apis::apiCallback('1002','暂无数据');
        }

        foreach ($data as $k =>&$item){

            if (!empty($item['cl_logo'])){
                $item['cl_logo'] =TFS_APIURL.'/'.$item['cl_logo'];
            }
            $data[$k]=self::convertkey($item);

        }

        return apis::apiCallback('1001',$data);

    }




    public function run_bak(){

        (!isset($this->options['userId']) || empty(intval($this->options['userId']))) && apis::apiCallback('1002','用户ID不能为空!');

        $courseId = intval($this->options['courseId']);
        if (!empty($courseId)){
            $whereClassId =" and cta_courseId={$courseId}";
        }

        $db=new MySql();
        $userId= $this->options['userId'];
        $sql="SELECT * FROM tang_attendance ta LEFT JOIN (SELECT tct.*, tc.co_name AS coName,tc.co_logo as coLogo, ttr.tra_name AS traName FROM tang_class_table tct LEFT JOIN tang_course tc ON tct.cta_courseId = tc.co_id LEFT JOIN tang_trainingsite ttr ON ttr.tra_id = tct.cta_trainingsiteId ) temp ON ta.att_classTableId =temp.cta_id WHERE ta.att_userId ={$userId}".$whereClassId;

        $data =$db->getAll($sql);
        if (empty($data)){
            return apis::apiCallback('1002','暂无学习记录数据!');
        }

        foreach ($data as $k =>&$item){
            if (($item['att_createTime']<date('Y-m-d H:i:s',strtotime($item['cta_startTime'])+600)) && ($item['att_createTime']<$item['cta_endTime']) ){
                $data[$k]['attType']='sigin';
            }else{
                $data[$k]['attType']='sigout';
            }
            $item['coLogo'] =TFS_APIURL.'/'.$item['coLogo'];
        }

        foreach ($data as $k => &$item){
            $data[$k] =self::convertkey($item);
        }
        apis::apiCallback('1001',$data);
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