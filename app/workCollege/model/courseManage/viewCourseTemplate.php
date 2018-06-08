<?php
/*=============================================================================
#     FileName: viewCourseTemplate.php
#         Desc: 修改课程模板
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:45:54
#      History:
#      Paramer:
=============================================================================*/
class viewCourseTemplate extends worker {
    function __construct($options) {
        parent::__construct($options, [50010402]);
    }

    function run() {
        $options = $this->options;
        $db      = new MySql();

        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show('<strong class="font-red">参数错误</strong>'));
        }

        $data = array(
            'code' => '10402',
            'id'   => $options['id'],
        );

        $courseList = $db->getField("SELECT ctt_course FROM tang_class_table_templet WHERE ctt_id='{$options['id']}'");

        if (!empty($courseList)) {
            $courseList = $db->getAll("SELECT co_name,co_credit,co_hour,co_state FROM tang_course WHERE co_id IN({$courseList})");
            if (!empty($courseList)) {
                array_walk($courseList,function(&$v){
                    $v['state'] = -1 == $v['co_state'] ? '无效' : '有效';
                });
            }
        }
        
        $this->setReplaceData($data);
        $this->setLoopData('courseList',$courseList);
        $this->setTempAndData();
        $this->show();
    }
}
