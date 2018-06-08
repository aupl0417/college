<?php
/*=============================================================================
#     FileName: editEnroll.json.php
#         Desc: 修改报名内容
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-12-20 10:51:00
#      History:
=============================================================================*/

class editEnroll_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50010303]);
    }

    function run() {
        $options = $this->options;

        $needParamer = array(
            'clID'            => '课程ID',
            'name'            => '名称',
            'allowableNumber' => '学员数量',
            'logo'            => '班级logo',
            'headmasterId'    => '班主任',
            //'cost'          => '学费',
            'enrollStartTime' => '报名开始时间',
            'enrollEndTime'   => '报馆结束时间',
            'startTime'       => '课程开始时间',
            'endTime'         => '课程结束时间',
            'condition'       => '学前条件',
            'description'     => '班级描述',
            'catering'        => '餐饮方式',
            'hostel'          => '住宿情况',
            'course'          => '课程',
            'hour'            => '课时',
            'credit'          => '学分',
        );

        foreach ($needParamer as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}错误，请检查后重试")));
            }

            if (!is_array($options[$k]) && $k != 'clID') {
                $data['cl_'.$k] = trim($options[$k]);
            }
        }

        //if (!isset($options['status'])) {
        //    die($this->show(message::getJsonMsgStruct(1002,"状态错误，请检查后重试")));
        //}

        $data['cl_cost']   = floatval($options['cost']);
        //$data['cl_status'] = intval($options['status']);

        if (!isset($options['tangCollege']) || empty($options['tangCollege'])) {
            die($this->show(message::getJsonMsgStruct(1002,'请选择分院')));
        }

        $data['tangCollege']  = intval($options['tangCollege']);
        $data['cl_condition'] = join(',',$options['condition']);

        if (isset($options['hostelMemo']) && !empty($options['hostelMemo'])) {
            $data['cl_hostelMemo'] = trim($options['hostelMemo']);
        }

        if (isset($options['cateringMemo']) && !empty($options['cateringMemo'])) {
            $data['cl_cateringMemo'] = trim($options['cateringMemo']);
        }
        
        if (isset($options['enrolledCount']) && !empty($options['enrolledCount'])) {
            $data['cl_enrolledCount'] = $options['enrolledCount'] + 0;
        }

        //报名条件
        $enrollCondition = array(
            'levelCondition' => intval($options['levelCondition']),
            'isAuthed'       => intval($options['isAuthed']),
            'enrollEver'     => intval($options['enrollEver']),
            'isBlack'        => intval($options['isBlack']),
        );

        $data['cl_enrollCondition'] = serialize($enrollCondition);

        $db = new MySql();

        //班级编号是否重复
        if ($db->count('tang_class',"cl_number='{$options['number']}' AND cl_id<>'{$options['clID']}'")) {
            die($this->show(message::getJsonMsgStruct(1002,'班级编号重复，请检查后重试')));
        }

        unset($v);
        if ($db->count('tang_class',"cl_name='{$options['name']}' AND cl_id<>'{$options['clID']}'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的班级名，请检查后再修改')));
        }

        $now = $data['cl_updateTime'] = F::mytime();

        try{
            $db->beginTRAN();
            $clID = intval($options['clID']);

            $insertClassRes = $db->update('tang_class',$data," cl_id='{$clID}'");

            if (1 != $insertClassRes) {
               throw new Exception('修改报名信息失败','1002');
            }

            //原课程
            $originalCourse = $db->getAll("SELECT cc_courseId FROM tang_class_course WHERE cc_classId='{$clID}'");
            if (empty($originalCourse)) {
                throw new Exception('获取原课程失败','1002');
            }
            $originalCourse = array_column($originalCourse,'cc_courseId');

            //保留的课程
            $keepCourse = array_intersect($options['course'], $originalCourse);

            //要删除的课程
            $delCourse = array_diff($originalCourse,$keepCourse);
            if (!empty($delCourse)) {
                $delCourse = implode(',',$delCourse);
                $delCourseRes = $db->delete('tang_class_course',"cc_classId='{$clID}' AND cc_courseId='{$delCourse}'");

                if (1 > $delCourseRes) {
                   throw new Exception('修改课程失败','1002');
                }
            }

            //新增的课程
            $newCourse = array_diff($options['course'],$originalCourse);

            if (!empty($newCourse)) {
                $newCourse = array_unique($newCourse);
                foreach ($options['course'] as $k=>$v) {
                    if (!isset($course[$v]) && !empty($v) && in_array($v,$newCourse)) {
                        $course[$v] = array($clID,$v,$options['hour'][$k],$options['credit'][$k],$now);
                    }
                }

                $insertCourseRes = $db->inserts('tang_class_course',array('cc_classId','cc_courseId','cc_hour','cc_credit','cc_createTime'),$course);
                if (1 > $insertCourseRes) {
                    throw new Exception('添加课程失败','1002');
                }
            }

            //if (1 == $options['state']) {
            //    $addClassRes = $this->addClass($clID,$db);
            //    if (!$addClassRes) {
            //        throw new Exception('添加班级失败','1002');
            //    }
            //}

            $db->commitTRAN();
            $this->show(message::getJsonMsgStruct(1001,'添加成功'));
        }catch(Exception $e){
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct($e->getCode(),$e->getMessage())));
        }
    }

    //审核通过
    private function addClass($clID,$db){
        $classInfo = $db->getRow("SELECT * FROM tang_class_temp WHERE cl_id='{$clID}'");
        unset($classInfo['cl_codeImage']);
        $classInfo['cl_state'] = 0;
        $res = $db->insert('tang_class',$classInfo);

        return (1 == $res);
    }
}
