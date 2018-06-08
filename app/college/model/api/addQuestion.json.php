<?php
/*=============================================================================
#     FileName: addQuestion.json.php
#         Desc: 提交提问
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-29 20:27:52
#      History:
#      Paramer: 
=============================================================================*/
/**
 * @api                    {post} /api/addQuestion.json 学员提问
 * @apiDescription         学员提问
 * @apiName                addQuestion_json
 * @apiGroup               Interaction
 *
 * @apiParam {int}        appId        设备类型
 * @apiParam {string}     deviceID     设备id
 * @apiParam {string}     signValue    签名串
 * @apiParam {string}     classId      班级id
 * @apiParam {string}     courseId     课程id
 * @apiParam {string}     userId       用户id（tang_ucentermember表的id）
 * @apiParam {string}     content      内容
 *
 * @apiSuccess (Success 1001) {Int} code 状态
 * @apiSuccess (Success 1001) {Int} msg  提示信息
 * @apiSuccess (Success 1001) {Int} data 数据
 *
 * @apiSuccessExample      Success-Response:
 *{
    "code": 1001,
    "msg": "获取成功",
    "data":
    
 }
 *
 * @apiErrorExample     Error-Response:
 *  {
 *       code: 1002,
         msg: "提交失败",
         data: ""
 }
 */
class addQuestion_json extends api {
    private $db;

    function run() {
        $options = $this->options;
        $needParams = array(
            'classId'  => '班级id',
            'courseId' => '课程id',
            'userId'   => '学员id',
            'title'    => '标题',
            'content'  => '内容',
        );

        foreach ($needParams as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                return apis::apiCallback(1002,$v.'错误');
            }
            $insertData['tsi_'.$k] = $options[$k];
        }

        $db = new MySql();
        
        $state = $db->getField('select cl_state from tang_class where cl_id="' . intval($options['classId']) . '" and cl_status=1');
        if($state != 1){
            return apis::apiCallback('1002', '班级未开课不能提问');
        }
        
        $count = $db->getField('select count(cs_id) from tang_class_student where cs_classId="' . intval($options['classId']) . '" and cs_studentId="' . intval($options['userId']) . '"');
        if(!$count){
            return apis::apiCallback('1002', '您不在该班级');
        }

        $insertData['tsi_teacherId'] = $db->getField("SELECT cta_teacherId FROM tang_class_table WHERE cta_classId='{$options['classId']}' AND cta_courseId='{$options['courseId']}'");
        $insertData['tsi_createTime'] = F::mytime();

        $res = $db->insert('tang_teacher_student_interaction',$insertData);

        if (1 != $res) {
            return apis::apiCallback('1002','提交失败','数据未能添加入数据库');
        }

        return apis::apiCallback('1001','提交成功');
    }
}
