<?php
/*=============================================================================
#     FileName: importTeacher.json.php
#         Desc: 导入讲师
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:50:49
#      History:
#      Paramer:
=============================================================================*/

class importTeacher_json extends worker {

    function __construct($options) {
        parent::__construct($options, [50030105]);
    }

    function run() {
		$options = $this->options;
        $needParams = array(
            'source'         => '来源',
            'userName'       => '昵称',
            'trueName'       => '姓名',
            'mobile'         => '手机',
            'IDNum'          => '身份证号',
            'teacherLevel'   => '讲师等级',
            'description'    => '简介',
            'workExperience' => '工作经历',
            'courseReward'   => '课酬',
            'teachGrade'     => '授课方式',
            'branchId'       => '所属分院',
            'eduLevel'       => '讲师学历',
            'userId'         => 'ID参数',
        );

        foreach ($needParams as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct('1002', $v.'错误，请检查后再重试')));
            }
        }

        $email    = isset($options['email']) && !empty($options['email']) ? trim($options['email']) : '';
        $birthday = isset($options['birthday']) && !empty($options['birthday']) ? trim($options['birthday']) : '';

        $db = new MySql();
        $now = F::mytime();

        if ($db->count('tang_ucenter_member',"userId='{$options['userId']}'") > 0) {
            die($this->show(message::getJsonMsgStruct('1002', '已经存在相同的老师信息，无需重复导入')));
        }

        try{
            $db->beginTRAN();
            $insertCenterData = array(
                'userId'       => $options['userId'],
                'username'     => $options['userName'],
                'trueName'     => $options['trueName'],
                'email'        => $email,
                'identityType' => 1,
                'tangCollge'   => $options['branchId'],
                'mobile'       => $options['mobile'],
                'certNum'      => $options['IDNum'],
                'reg_time'     => $now,
            );


            $insertCenterRes = $db->insert('tang_ucenter_member',$insertCenterData);
            if (1 != $insertCenterRes) {
               throw new Exception('添加讲师失败','-1');
            }

            //添加老师档案信息
            $insertTeacherData = array(
                'te_userId'         => $db->getLastID(),
                'te_source'         => intval($options['source']),
                'te_IDNum'          => trim($options['IDNum']),
                'te_workExperience' => trim($options['workExperience']),
                'te_sex'            => intval($options['sex']),
                'te_courseReward'   => intval($options['courseReward']),
                'te_teachGrade'     => intval($options['teachGrade']),
                'te_level'          => intval($options['teacherLevel']),
                'te_eduLevel'       => intval($options['eduLevel']),
                'te_birthday'       => trim($options['birthday']),
                'te_description'    => trim($options['description']),
            );

            $insertTeacherRes = $db->insert('tang_teacher',$insertTeacherData);
            if (1 != $insertTeacherRes) {
                throw new Exception('添加讲师档案失败','-2');
            }

            if (1 == $options['userPower']) {
                $defaultPower = '50,5001,50101,50010101';
                $employeeInfo = array(
                    'e_id'         => trim($options['userName']),
                    'e_uid'        => F::getGID(),
                    'e_name'       => trim($options['trueName']),
                    'e_certNum'    => trim($options['IDNum']),
                    'e_tel'        => trim($options['mobile']),
                    'e_createTime' => $now,
                    'e_powerList'  => $defaultPower,
                    'e_powerHash'  => F::powerHash($defaultPower),
                    'e_loginPwd'   => F::getSuperMD5(substr(trim($options['certNum']),-6)),
                );

                $insertEmployeeRes = $db->insert('tang_employee',$employeeInfo);
                if (1 != $insertEmployeeRes) {
                    throw new Exception('添加唐人大学系统管理账号失败','-1');
                }
            }

            $db->commitTRAN();
            $this->show(message::getJsonMsgStruct('1001', '操作成功'));
        }catch(Exception $e){
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002', $e->getMessage()));
        }
    }
}
