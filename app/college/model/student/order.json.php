<?php

class order_json extends member {

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
        $uid = $this->db->getField('select id from tang_ucenter_member where userId="' . $userId . '"');

        $dataGrid  = new DataGrid();

        $op = '<a href="/%s/?id=%s" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        $opStr = '<a href="/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        $field = 'tse_id as DT_RowId,tse_createTime as createTime,cl_name as className,tse_fee as fee,tse_payFee as payFee,tse_state as state,tse_status as status,CASE tse_status WHEN -1 THEN "已取消" WHEN 0 THEN "未支付" WHEN 1 THEN "已支付" WHEN 2 THEN "已报到" ELSE "已转让" END as enrollStatus';
        $sql = "select " . $field . " from tang_student_enroll
                LEFT JOIN tang_class on cl_id=tse_classId
                where tse_userId='" . $uid . "' and tse_state<>-1";

        $result = $dataGrid->create($this->options, $sql);
        if($result['data']){
            foreach($result['data'] as $key=>&$val){
                $val['op'] = sprintf($opStr, 'student/detail', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '详情');
                //这些功能暂时不做
                /* if($val['status'] == 0){
                    $val['op'] .= sprintf($opStr, 'student/pay', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '支付');
                    $val['op'] .= sprintf($opStr, 'student/cancel', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '取消');
                }else if($val['status'] == 1 and $val['state'] == 1){
                    $val['op'] .= sprintf($opStr, 'student/trainsClass', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '转班');
                    $val['op'] .= sprintf($opStr, 'student/trainsOrder', $val['DT_RowId'], '#temp-modal-power', 'fa-edit', '转人');
                } */
                $val['state'] = $val['state'] == 0 ? '未审核' : ($val['state'] == 1 ? '通过审核' : ($val['state'] == -1 ? '审核拒绝' : ''));
            }
        }

        echo json_encode($result);
    }

}
