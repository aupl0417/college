<?php

class classList_json extends member {

    function __construct($options) {
        parent::__construct($options, [50102]);
        $this->db = new MySql();
    }

    function run() {
        $option = $this->options;
        if(!$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $userId = $_SESSION['userID'];
//        $userId = 'aaa263f7936899d2ba78ab57d7a06844';
        $uid = $this->db->getField('select id from tang_ucenter_member where userId="' . $userId . '"');

        $dataGrid  = new DataGrid();

        $op = '<a href="/%s/?id=%s" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        $opStr = '<a href="/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        $field = 'cl_id as DT_RowId,cl_id as id,cl_name as className,cl_logo as logo,cl_startTime as startTime,cl_endTime as endTime,cl_description as description,username as teacher,mobile,CASE cl_state WHEN 0 THEN "报名中" WHEN 1 THEN "开课中" ELSE "已结束" END as state';
        $sql = "select " . $field . " from tang_class_student
                LEFT JOIN tang_class on cl_id=cs_classId
                left join tang_ucenter_member on cl_headmasterId=id
                where cs_studentId='" . $uid . "' and cl_state<>-1 and cl_status=1 and cl_isTest=0";
        $result = $dataGrid->create($this->options, $sql);

        if($result['data']){
            foreach($result['data'] as $key=>&$val){
                $val['op']  = '<p>' . sprintf($op,'class/detail', $val['DT_RowId'], 'fa-edit', '课程详情') . '</p>';
                $val['op'] .= '<p>' . sprintf($opStr,'student/tableList', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '课程表')   . '</p>';
                $val['op'] .= '<p>' . sprintf($opStr,'student/interaction', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '我的提问') . '</p>';
                $val['op'] .= '<p>' . sprintf($opStr,'student/learnRecord', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '学习情况') . '</p>';
                $val['logo'] = "<img src='" . TFS_APIURL . '/' . $val['logo'] . "' width='227px;height:158px;'>";

                $val['classInfo']  = '<div class="form-group">';
                $val['classInfo'] .=    '<div class="col-md-10">';
                $val['classInfo'] .=        '<label style="line-height:24px;"><h4>' . $val['className'] . '　【' . $val['state'] . '】</h4></label>';
                $val['classInfo'] .=    '</div>';
                $val['classInfo'] .=            '<div class="col-md-10">';
                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">开课时间：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-3">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['startTime'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">结束时间：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-3">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['startTime'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=            '</div>';
                $val['classInfo'] .=            '<div class="col-md-10">';
                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">班级编号：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-3">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['id'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">本班班主任：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-3">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . $val['teacher'] . '</label>';
                $val['classInfo'] .=                    '　　手机号码：<label style="line-height:24px;font-size: 10px;">' . $val['mobile'] . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=            '</div>';
                $val['classInfo'] .=            '<div class="col-md-10">';
                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">课程简介：</label></label>';
                $val['classInfo'] .=                '<div class="col-md-7">';
                $val['classInfo'] .=                    '<label style="line-height:24px;font-size: 10px;">' . F::TextToHtml($val['description']) . '</label>';
                $val['classInfo'] .=                '</div>';
                $val['classInfo'] .=            '</div>';
                $val['classInfo'] .=            '<div class="col-md-10">';
                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">' . sprintf($opStr,'student/attendList', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '签到记录') . '</label></label>';
//                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">' . sprintf($opStr,'class/detail', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '课件下载') . '</label></label>';
                $val['classInfo'] .=                '<label class="control-label col-md-3"><label style="font-size: 10px;">' . sprintf($op,'glimpse/detail', $val['DT_RowId'], 'fa-edit', '花絮记录') . '</label></label>';
                $val['classInfo'] .=            '</div>';
                $val['classInfo'] .= '</div>';
            }
        }


        echo json_encode($result);
    }

}
