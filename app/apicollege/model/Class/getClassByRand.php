<?php
/**
 * @api                    {post} /Class/getClassByRand 随机获取班级
 * @apiDescription         随机获取3个班级
 * @apiName                getClassByRand
 * @apiGroup               Class
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId      设备类型
   @apiParam {string}     deviceID   设备id
   @apiParam {string}     signValue  签名串
   @apiParam {string}     userId     用户id
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: [
        {
            id: "31",
            className: "高级课程",
            classLogo: "https://image.dttx.com/v1/tfs/T1KmDTB4dT1RCvBVdK.jpg",
            enrollStartTime: "2016-10-24",
            enrollEndTime: "2016-10-31",
            enrollStatus: 0
        },
        {
            id: "31",
            className: "高级课程",
            classLogo: "https://image.dttx.com/v1/tfs/T1KmDTB4dT1RCvBVdK.jpg",
            enrollStartTime: "2016-10-24",
            enrollEndTime: "2016-10-31",
            enrollStatus: 0
        },
        {
            id: "31",
            className: "高级课程",
            classLogo: "https://image.dttx.com/v1/tfs/T1KmDTB4dT1RCvBVdK.jpg",
            enrollStartTime: "2016-10-24",
            enrollEndTime: "2016-10-31",
            enrollStatus: 0
        }
    ]
  }
 *
 */
class getClassByRand extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        $classIds = $this->db->getAll('select cl_id as id from tang_class where cl_state in(0,1,2) and cl_status=1 and cl_isTest=0 order by cl_createTime desc limit 100');
        $classIds = array_column($classIds, 'id');
        $data = array_slice($classIds,0,3);
        
        $sql = 'SELECT cl_id as id,cl_name as className,cl_logo as classLogo,cl_enrollStartTime as enrollStartTime,cl_enrollEndTime as enrollEndTime FROM `tang_class` 
                WHERE cl_id in (' . implode(',', $data) . ')
                and cl_state<>-1 and cl_status=1 ORDER BY cl_startTime DESC';
        
        $classList = $this->db->getAll($sql);

        !$classList && $this->apiReturn(1002, '暂无结果');

        foreach ($classList as &$val){
            $val['classLogo'] = $val['classLogo'] ? TFS_APIURL . '/' . $val['classLogo'] : '';
            $val['enrollStatus'] = 0;
            if($this->uid){
                $enroll = $this->db->getRow('select tse_status from tang_student_enroll where tse_classId="' . $val['id'] . '" and tse_userId="' . $this->uid . '" order by tse_id desc limit 1');
                if(!$enroll){
                    $val['enrollStatus'] = 0;
                }else {
                    $val['enrollStatus'] = 1;
                }
            }
        }
        
        $this->apiReturn(1001, '', $classList);
    }
}
