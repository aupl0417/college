<?php
/**
 * @api                    {post} /api/getClassPicture.json 获取班级学员风采图片列表
 * @apiDescription         获取班级学员风采图片列表
 * @apiName                getClassPicture.json
 * @apiGroup               Class
 * @apiPermission          lirong
 *

@apiParam {int}        classId   班级id
 *
 *
 * @apiSuccessExample      Success-Response:
 *
    {
    "code": "1001",
    "data": [
            {
            "tcp_id": "45",
            "tcp_classId": "136",
            "tcp_filename": "http://192.168.3.201:80/v1/tfs/T17txTB4YT1RCvBVdK.jpg",
            "tcp_sort": "6",
            "tcp_isLogo": "1"
            }
        ]
    }
 *
 * @apiErrorExample        Error-Response:
 *
 * {
    "code": "1002",
    "data":'暂无数据'
    }
 *
 */
class getClassPicture_json extends api{

    public function run(){

        if(!isset($this->options['classId']) || empty(intval($this->options['classId']))){
            return apis::apiCallback('1002','班级ID不能为空!');
        }

        $db =new MySql();
        $classId=$this->options['classId'];
        $sql="SELECT * FROM `tang_class_picture` WHERE tcp_classId =$classId ORDER BY tcp_isLogo DESC,tcp_sort ASC";

        $data = $db->getAll($sql);

        foreach ($data as &$item){
            $item['tcp_filename'] =!empty($item['tcp_filename']) ? TFS_APIURL.'/'.$item['tcp_filename'] : "";
        }

        if (empty($data)){
            return apis::apiCallback('1002','暂无数据!');
        }

        return apis::apiCallback('1001',$data);
    }

}