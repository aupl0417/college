<?php
/**
 * @api                    {post} /api/classCollect.json 用户班级收藏
 * @apiDescription         用户班级收藏
 * @apiName                classCollect.json
 * @apiGroup               User
 * @apiPermission          aupl
 *
 * @apiParam {int}        appID     设备类型
   @apiParam {string}     deviceId  设备id
   @apiParam {string}     signValue 签名串
   @apiParam {string}     userId    学员id
   @apiParam {string}     classId   班级id
   @apiParam {int}        col       是否收藏(1:收藏；0：取消收藏)
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "收藏成功",
    data: null 
  }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: 1002,
    msg: "您已收藏该课",
    data: null
  }
 *
 */
class classCollect_json extends api {

    function run() {
        (!isset($this->options['userId']) || empty($this->options['userId'])) && $this->apiReturn(404);

        
        $isCollect = isset($this->data['col']) ? $this->data['col'] + 0 : 1;
        $classId  = $this->data['classId'] + 0;
        $deviceId = $this->data['deviceID'];
        $count = $this->db->getField('select count(cl_id) from tang_class where cl_id="' . $classId . '" and cl_state in(0,1) and cl_status=1');
        !$count && $this->apiReturn(501, '该班级不存在');
        
        if($isCollect == 1){
            $count = $this->db->getField('select count(tcc_id) from tang_class_collection where tcc_classId="' . $classId .'" and tcc_userId="' . $this->uid .'"');
            $count && $this->apiReturn(1002, '您已收藏该班级');
            
            $data = array(
                'tcc_classId'  => $classId,
                'tcc_userId'   => $this->uid,
                'tcc_deviceId' => $deviceId,
                'tcc_createTime' => date('Y-m-d H:i:s')
            );
            
            $res = $this->db->insert('tang_class_collection', $data);
            !$res && $this->apiReturn(1002, '收藏失败');
            $this->apiReturn(1001, '收藏成功');
        }else {
            $count = $this->db->getField('select count(tcc_id) from tang_class_collection where tcc_classId="' . $classId .'" and tcc_userId="' . $this->uid .'"');
            !$count && $this->apiReturn(1002, '您没有收藏该班级');
            
            $res = $this->db->delete('tang_class_collection', 'tcc_classId="' . $classId .'" and tcc_userId="' . $this->uid .'"');
            !$res && $this->apiReturn(1002);
            $this->apiReturn(1001);
        }
        
        
    }
}
