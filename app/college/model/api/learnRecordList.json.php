<?php

/* 获取学生学习记录
 * @param $userId    type : int 用户id   must
 * @param $classId   type : int 用户id
 * @param $state     type : string 类型
 * @author adadsa
 * @databases  tang_class_student，tang_branch
 * */

/**
 * @api                    {post} /api/learnRecordList.json 获取学生学习记录
 * @apiDescription         获取学生学习记录
 * @apiName                learnRecordList.json
 * @apiGroup               学员
 * @apiPermission          adadsa
 *

@apiParam {int}     userId       用户id
@apiParam {int}     classId       用户id
@apiParam {int}     state       签到/学习记录（study）
 *
 *
 * @apiSuccessExample      Success-Response:
 *
 * {
    "code": "1001",
    "data": [
        {
        "attId": "264",
        "attUserId": "300",
        "attClassTableId": "212",
        "attClassId": "123",
        "attCourseId": "67",
        "attBranchId": "2",
        "attCreateTime": "2016-11-14 16:51:04",
        "attState": "0",
        "coId": "67",
        "coLogo": "http://192.168.3.201:80/v1/tfs/T1AaCTBXAT1RCvBVdK.jpg",
        "coName": "test课程（2）",
        "coStudyDirectionId": "20",
        "coDescription": "test课程（2）\n课程描述",
        "coGradeID": "1",
        "coContent": "test课程（2）\n课程说明",
        "coState": "1",
        "coCreateTime": "2016-11-11 17:12:20",
        "coUpdateTime": "1970-01-01 00:00:00",
        "coCredit": "10",
        "coHour": "10",
        "brName": "长沙分院"
        }
    ]
    }
 *
 * @apiErrorExample        Error-Response:
 *{
code: 1002,
data: "暂无数据",
}
 *
 */
/**
 * Created by PhpStorm.
 * User: lirong
 * Date: 2016/11/20
 * Time: 16:50
 */
class learnRecordList_json extends api{

    public function run(){

        if(!isset($this->options['userId']) || empty(intval($this->options['userId']))){
           return apis::apiCallback('1002','用户ID不能为空!');

        }
        if ($this->options['state']=='study'){
            $group ="  GROUP BY att_courseId ";
        }else{
            if(!isset($this->options['classId']) || empty(intval($this->options['classId']))){
                apis::apiCallback('1002','班级ID不能为空!');
                exit();
            }
        }
        $classId =$this->options['classId'];
        if (!empty($classId)){
            $where="att_classId =$classId AND ";
        }

        $db =new MySql();
        $userId =$this->options['userId'];
        $sql="SELECT ta.*,tc.*,br_name FROM tang_attendance ta LEFT JOIN tang_course tc ON ta.att_courseId =tc.co_id  LEFT JOIN tang_branch tb ON ta.att_branchId = tb.br_id WHERE $where att_userId =$userId $group ORDER BY att_id DESC";

        $data =$db->getAll($sql);

        if (empty($data)){
            return apis::apiCallback('1002','暂无数据');
        }

        foreach ($data as $k =>&$item){

            if (!empty($item['co_logo'])){
                $item['co_logo'] =TFS_APIURL.'/'.$item['co_logo'];
            }
            $data[$k]=self::convertkey($item);

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