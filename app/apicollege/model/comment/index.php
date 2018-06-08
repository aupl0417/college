<?php
/**
 * @api                    {post} /comment/index 评论接口
 * @apiDescription         用户评论某个课时的讲师或者班级
 * @apiName                index
 * @apiGroup               comment
 * @apiPermission          aupl
 *
 * @apiParam {int}      appId     设备类型
   @apiParam {string}   deviceID  设备id
   @apiParam {string}   signValue 签名串
 * @apiParam {int}   	id        课时id     可选（传id 就不传classId）
   @apiParam {string}   userId    学员id     必需
   @apiParam {string}   content   评论内容             必需
   @apiParam {int}      level     评论星级             可选  默认为5 
   @apiParam {int}      classId   班级id     可选（传了classId 就不传id）
   @apiParam {int}      type      评论用户类型     可选（0：非匿名评论；1：匿名评论；   默认1）
 *
 *
 * @apiSuccessExample      Success-Response:
 *	{
        code: 1001,
        msg: "评论成功",
        data: null
    }
 *
 */
class index extends newBaseApi{
    
    function __construct($options) {
        parent::__construct($options);
		$this->nowTime = time();
    }
	
    function run() {
        (!isset($this->data['userId']) || empty($this->data['userId'])) && $this->apiReturn(404);
        (!isset($this->data['content']) || empty($this->data['content'])) && $this->apiReturn(303);
        
        !$this->uid && $this->apiReturn(1002, '您未报名任何班级');
        
        $userType = isset($this->data['type']) ? $this->data['type'] + 0 : 1;
        $level   = isset($this->data['level']) ? $this->data['level'] + 0 : 5;
        ($level > 5 || $level < 1) && $this->apiReturn(304);
        
        $data = array(
            'tc_userId'          => $this->uid,
            'tc_content'         => $this->data['content'],
            'tc_level'           => $level,
            'tc_createTime'      => date('Y-m-d H:i:s', $this->nowTime)
        );
        
        if(isset($this->data['id']) && !empty($this->data['id'])){
            $id = $this->data['id'] + 0;
            $classTable = $this->db->getRow('select cta_teacherId,cta_classId from tang_class_table where cta_id="' . $id . '"');
            $field = 'tc_classTableId';
            $data['tc_classId']   = $classTable['cta_classId'];
            $data['tc_teacherId'] = $classTable['cta_teacherId'];
            $data['tc_classTableId'] = $id;
        }else if(isset($this->data['classId']) && !empty($this->data['classId'])){
            $id = $this->data['classId'] + 0;
            $field = 'tc_classId';
            $data['tc_classId'] = $id;
            $data['tc_classTableId'] = 0;
            $data['tc_type'] = $userType;
        }
        
//         $sql = 'select count(tc_id) from tang_teacher_comment where tc_userId="' . $this->uid . '" and ' . $field . '="' . $id . '"';
//         $count = $this->db->getField($sql);
//         $count && $this->apiReturn(305);
        
        
        $res = $this->db->insert('tang_teacher_comment', $data);
        !$res && $this->apiReturn(302);
        $this->apiReturn(1001);
    }
}
