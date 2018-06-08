<?php
/*=============================================================================
#     FileName: attendanceList.json.php
#         Desc: 班级学员签到表
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage: 
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:48:19
#      History:
=============================================================================*/
class attendanceList_json extends worker{
    public function __construct($options){
        parent::__construct($options,[500103]);
    }

    public function run(){
        $emptyInfo = array(
            'draw'            => 0,
            'recordsTotal'    => 0,
            'recordsFiltered' => 0,
            'data'            => [],
        );

        $options = &$this->options;
        if (!isset($options['clID']) || empty($options['clID'])) {
            die(json_encode($emptyInfo));
        }

        $clID = intval($options['clID']);
        $where = "WHERE cta_classId='{$clID}' ";

        if (isset($options['search'])) {
            if (isset($options['search']['courseID'])) {
                $coID = trim($options['search']['courseID']['value']);
                $where .= " AND cta.cta_courseId={$coID}";
                unset($options['search']['courseID']);
            }

            if (isset($options['search']['trueName'])) {
                $trueName = trim($options['search']['trueName']['value']);
                $where .= " AND um.trueName='{$trueName}'";
                unset($options['search']['trueName']);
            }

        }
        $dataGrid = new DataGrid();

        $sql    = "SELECT att.att_createTime createTime,um.truename,um.username,cta_startTime startTime,cta_endTime endTime,co.co_name
            FROM tang_attendance att LEFT JOIN tang_ucenter_member um ON um.id=att.att_userId
            LEFT JOIN tang_class_table cta ON att.att_classTableId=cta.cta_id
            LEFT JOIN tang_course co ON co.co_id=cta.cta_courseId $where";

        $result = $dataGrid->create($this->options,$sql);

        if (!$result['data']) {
            die(json_encode($emptyInfo));
        }

        foreach ($result['data'] as &$v) {
            $v['DT_RowId']   = 'row_'.$v['cs_id'];
        }
        echo json_encode($result);
    }
}
