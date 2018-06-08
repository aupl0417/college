<?php
/**
 * @api                    {post} /Course/detail 课程详情
 * @apiDescription         课程详情
 * @apiName                detail
 * @apiGroup               Course
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {int}        id         课程id
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        id: "65",
        courseName: "联盟商家展业培训",
        logoUrl: "https://image.dttx.com/v1/tfs/T15EKTB7ZT1RCvBVdK.jpg",
        descriptions: "1.C+商业模式剖析 2.C+商业模式收益分配 3.解读经典案例 4.如何成为联盟商家 项目部对接说明",
        content: "1.C+商业模式剖析 2.C+商业模式收益分配 3.解读经典案例 4.如何成为联盟商家 项目部对接说明",
        credit: "10",
        hour: "10",
        classType: "电子商务",
        gradeName: "初级",
        minutes: "600"
    }
   }
 *
 */
class detail extends baseApi{
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(604);
        
        $courseId = $this->data['id'] + 0;
        
        $field = 'co_id as id,co_name as courseName,co_logo as logoUrl,co_description as descriptions,co_content as content,co_credit as credit,co_hour as hour,sd_name as classType,gr_name as gradeName';
        $sql = 'select ' . $field . ' from tang_course 
               LEFT JOIN tang_study_direction on co_studyDirectionId=sd_id 
               LEFT JOIN tang_grade on gr_id=co_gradeID 
               where co_state=1 and co_id="' . $courseId . '"';
        $course = $this->db->getRow($sql);
        !$course && $this->apiReturn(1002, '暂无课程');
        
        $course['descriptions'] = trim($course['descriptions']);
        $course['content']      = trim($course['content']);
        $course['minutes']      = $course['hour']; 
        $course['logoUrl']      = $course['logoUrl'] ? TFS_APIURL . '/' . $course['logoUrl'] : '';
        
        $this->apiReturn(1001, '', $course);
    }
}
