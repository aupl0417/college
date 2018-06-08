<?php
/*=============================================================================
#     FileName: addClassSchedule.json.php
#         Desc: 添加排课
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:48:41
#      History:
=============================================================================*/

class addClassSchedule_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500102]);
    }

    function run() {
        $options = $this->options;
        $db      = new MySql();

        $needParamer = array(
            'classID'      => '班级ID',
            'course'       => '课程',
            'scheduleDate' => '上课日期',
            'startTime'    => '上课开始时间',
            'endTime'      => '上课结束时间',
            'classroom'    => '教室',
            'teacher'      => '主讲老师',
        );

        foreach ($needParamer as $k=>$v) {
            if (!isset($options[$k]) || empty($options[$k])) {
                die($this->show(message::getJsonMsgStruct(1002,"{$v}错误，请检查后重试")));
            }

            $needParamer[$k] = $options[$k];
        }

        extract($needParamer);
        $checkRes = $this->checkSchedule($needParamer);

        if (!$checkRes) {
            die($this->show(message::getJsonMsgStruct(1002,'排课信息不完善,请检查后重试')));
        }

        //时间冲突检测
        for ($i = 0,$length=count($options['scheduleDate']);  $i < $length; $i++) {
            $startTime1 = strtotime($scheduleDate[$i].' '.$startTime[$i]);
            $endTime1 = strtotime($scheduleDate[$i].' '.$endTime[$i]);

            if ($startTime1 > $endTime1) {
                die($this->show(message::getJsonMsgStruct(1002,"上课时间不能晚于下课时间")));
            }

            for ($j = $i+1; $j < $length; $j++) {
                $startTime2 = strtotime($scheduleDate[$j].' '.$startTime[$j]);
                $endTime2 = strtotime($scheduleDate[$j].' '.$endTime[$j]);

                if ($startTime1 >= $endTime2 || $endTime1 <= $startTime2) {
                    continue;
                }else{
                    die($this->show(message::getJsonMsgStruct(1002,"上课时间冲突,请检查后再添加")));
                }
            }
        }

        $now = F::mytime();
        foreach ($course as $k=>$v) {
            $insertData[] = array(
                $options['classID'],
                $v,
                $scheduleDate[$k].' '.$startTime[$k],
                $scheduleDate[$k].' '.$endTime[$k],
                $teacher[$k],
                $classroom[$k],
                $now
            );
        }

        $fields = array(
            'cta_classId',
            'cta_courseId',
            'cta_startTime',
            'cta_endTime',
            'cta_teacherId',
            'cta_trainingsiteId',
            'cta_createTime',
        );

        $res = $db->inserts("tang_class_table",$fields,$insertData);

        if (1 > $res) {
            die($this->show(message::getJsonMsgStruct(1002,'排课失败')));
        }
        
        $this->show(message::getJsonMsgStruct(1001,'排课成功'));
    }

    function checkSchedule($options){
        array_shift($options);
        $scheduleList = array_map(function($n){
            if (is_array($n)) {
                return count(array_filter($n));
            }

        },$options);

        if (count(array_unique($scheduleList)) > 1){
            return FALSE;
        }
        return TRUE;
    }
}
