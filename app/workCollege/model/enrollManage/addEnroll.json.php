<?php
/*=============================================================================
#     FileName: addEnroll.json.php
#         Desc: 发起报名
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-12-20 11:02:55
#      History:
=============================================================================*/

class addEnroll_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50010303]);
    }

    function run() {
        $options = $this->options;

        $needParamer = array(
            'name'            => '名称',
            'allowableNumber' => '学员数量',
            'headmasterId'    => '班主任',
            'logo'            => '班级logo',
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
            'number'          => '班级编号',
        );

        foreach ($needParamer as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}错误，请检查后重试")));
            }

            if (!is_array($options[$k])) {
                $data['cl_'.$k] = trim($options[$k]);
            }
        }

        if (!isset($options['tangCollege']) || empty($options['tangCollege'])) {
            die($this->show(message::getJsonMsgStruct(1002,'请选择分院')));
        }
        
        if (isset($options['enrolledCount']) && !empty($options['enrolledCount'])) {
            $data['cl_enrolledCount'] = $options['enrolledCount'] + 0;
        }

        $data['cl_cost']      = floatval($options['cost']);
        $data['cl_condition'] = join(',',$options['condition']);
        $data['tangCollege']  = intval($options['tangCollege']);

        if (isset($options['hostelMemo']) && !empty($options['hostelMemo'])) {
            $data['cl_hostelMemo'] = trim($options['hostelMemo']);
        }

        if (isset($options['cateringMemo']) && !empty($options['cateringMemo'])) {
            $data['cl_cateringMemo'] = trim($options['cateringMemo']);
        }

        unset($v);

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
        if ($db->count('tang_class',"cl_number={$options['number']}")) {
            die($this->show(message::getJsonMsgStruct(1002,'班级编号重复，请检查后重试')));
        }

        if ($db->count('tang_class',"cl_name='".$options['name']."'")) {
            die($this->show(message::getJsonMsgStruct(1002,'已经存在相同的班级名，无需重复添加')));
        }

        $now = $data['cl_createTime'] = F::mytime();

        try{
            $db->beginTRAN();
            $insertClassRes = $db->insert('tang_class',$data);
            if (1 != $insertClassRes) {
                throw new Exception('添加班级失败','1002');
            }

            $classID = $db->getLastID();

            $glimplse = array(
                'tcp_classId'  => $classID,
                'tcp_filename' => $data['cl_logo'],
                'tcp_title'    => '',
                'tcp_sort'     => 0,
                'tcp_isLogo'   => 1
            );
            
            if(!$db->insert('tang_class_picture', $glimplse)){
                throw new Exception('添加班级LOGO到学员风采表失败', '1002');
            }
            
            //课程
            foreach ($options['course'] as $k=>$v) {
                if (!isset($course[$v]) && !empty($v)) {
                    $course[$v] = array($classID,$v,$options['hour'][$k],$options['credit'][$k],$now);
                }
            }

            $insertCourseRes = $db->inserts('tang_class_course',array('cc_classId','cc_courseId','cc_hour','cc_credit','cc_createTime'),$course);
            if (1 > $insertCourseRes) {
                throw new Exception('添加课程失败','1002');
            }

            $db->commitTRAN();
            $this->show(message::getJsonMsgStruct(1001,'添加成功'));
        }catch(Exception $e){
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct($e->getCode(),$e->getMessage())));
        }
    }
}
