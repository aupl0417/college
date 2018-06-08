<?php
/*=============================================================================
#     FileName: addInteration.json.php
#         Desc: 提交提问
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-29 20:28:04
#      History:
#      Paramer: 
=============================================================================*/
/**
 * @api                    {post} /api/addInteration_json 添加互动留言
 * @apiDescription         添加互动留言
 * @apiName                addInteration
 * @apiGroup               Interaction
 *
 * @apiParam {int}        appId        设备类型 
 * @apiParam {string}     deviceID     设备id
 * @apiParam {string}     signValue    签名串
 * @apiParam {string}     classId      班级id
 * @apiParam {string}     courseId     课程id
 * @apiParam {string}     userId       用户id（tang_ucentermember表的id）
 * @apiParam {string}     content      内容
 * @apiParam {string}     interationParentId 上一个留言的ID
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
class addInteration_json extends api {
    private $db;

    function run() {
        $options = $this->options;
        $needParams = array(
            'classId'  => '班级id',
            'courseId' => '课程id',
            'userId'   => '学员id',
            'content'  => '内容',
        );

        foreach ($needParams as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                return apis::apiCallback(1002,$v.'错误');
            }
            $insertData['tsi_'.$k] = $options[$k];
        }

        $db = new MySql();
        $userType = isset($options['userType']) && !empty($options['userType']) ? $options['userType'] + 0 : 0;
        if (!isset($options['interationParentId']) || empty($options['interationParentId'])) {
            return apis::apiCallback('1002','提交失败','上一个留言的ID');
        }

        $teacherId = $db->getField("SELECT cta_teacherId FROM tang_class_table WHERE cta_classId='{$options['classId']}' AND cta_courseId='{$options['courseId']}'");
        
        $insertData['tsi_pid'] = intval($options['interationParentId']);
        $insertData['tsi_createTime'] = F::mytime();
        
        if($userType == 1){
            $insertData['tsi_teacherId'] = $options['userId'] + 0;
            unset($insertData['tsi_userId']);
        }
        
        $res = $db->insert('tang_teacher_student_interaction',$insertData);

        if (1 != $res) {
            return apis::apiCallback('1002','提交失败','数据未能添加入数据库');
        }

        return apis::apiCallback('1001','提交成功');
    }
}
