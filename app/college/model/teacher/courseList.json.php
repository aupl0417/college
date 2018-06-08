<?php

class courseList_json extends member {

    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        $option = $this->options;
        if(!$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $userId = $_SESSION['userID'];
        $uid = $this->db->getField('select id from tang_ucenter_member where userId="' . $userId . '"');

        $courseIds = $this->db->getAll('select cta_courseId,cta_classId from tang_class_table where cta_teacherId="' . $uid . '"');
        $courseIds = array_column($courseIds, 'cta_courseId', 'cta_classId');
        if(!$courseIds){
            $emptyInfo = array(
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            );
            
            die(json_encode($emptyInfo));
        }

        $dataGrid  = new DataGrid();

        $op = '<a href="/%s/?id=%s" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        $opStr = '<a href="/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        $field = 'co_id as DT_RowId,co_id as id,cl_id as cId,co_name as courseName,cl_startTime as startTime,cl_endTime as endTime,co_description as description,cl_name as className,tra_name as trainSite,te_courseReward';
        $sql = 'select ' . $field . ' from
                    (select DISTINCT cta_courseId,cta_classId,cta_trainingsiteId,cta_teacherId from tang_class_table
                        where cta_teacherId="' . $uid . '") as classTable
                        left join tang_course on cta_courseId=co_id
                        left join tang_class on cl_id=cta_classId
                        left join tang_trainingsite on cta_trainingsiteId=tra_id
                        left join tang_teacher on te_userId=cta_teacherId
                where 1=1';

        $result = $dataGrid->create($this->options, $sql);

        if($result['data']){
            foreach($result['data'] as $key=>&$val){
                $val['op']  = '<p>' . sprintf($op,'teacher/resourceList', $val['DT_RowId'], 'fa-edit', '课件管理') . '</p>';
//                 $val['op'] .= '<p>' . sprintf($opStr,'teacher/itemManage', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '考题管理')   . '</p>';
                $val['op'] .= '<p>' . sprintf($op,'teacher/studentManage', $val['id'] . '&cId=' . $val['cId'], 'fa-edit', '学员管理') . '</p>';
                $val['op'] .= '<p>' . sprintf($op,'teacher/interactionManage', $val['id'] . '&cId=' . $val['cId'], 'fa-edit', '答疑处理') . '</p>';

                $val['classInfo']  = '<div class="form-group">';
                $val['classInfo'] .=    '<div class="col-md-10">';
                $val['classInfo'] .=        '<label style="line-height:24px;"><h4>' . $val['courseName'] . '</h4></label>';
                $val['classInfo'] .=    '</div>';
                $val['classInfo'] .=            '<div class="col-md-12">';
                $val['classInfo'] .=                '<label class="control-label col-md-2"><label style="font-size: 10px;">开课时间：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-2">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['startTime'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=                '<label class="control-label col-md-2"><label style="font-size: 10px;">结束时间：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-2">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['startTime'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=                '<label class="control-label col-md-2"><label style="font-size: 10px;">所属班级：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-2">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['className'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=            '</div>';
                $val['classInfo'] .=            '<div class="col-md-12">';
                $val['classInfo'] .=                '<label class="control-label col-md-2"><label style="font-size: 10px;">课程编号：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-2">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['id'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=                '<label class="control-label col-md-2"><label style="font-size: 10px;">场地：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-2">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['trainSite'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=                '<label class="control-label col-md-2"><label style="font-size: 10px;">授课费用（￥）：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-2">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['te_courseReward'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=            '</div>';
                $val['classInfo'] .=            '<div class="col-md-10">';
                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">课程简介：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-7">';
                $val['classInfo'] .=                    F::TextToHtml($val['description']);
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=            '</div>';
                $val['classInfo'] .= '</div>';
            }
        }


        echo json_encode($result);
    }

}
