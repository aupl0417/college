<?php
/*=============================================================================
#     FileName: studentScoreList.json.php
#         Desc: 班级学员成绩列表
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:47:18
#      History:
=============================================================================*/
class studentScoreList_json extends worker{
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
        $where = '';
        if (isset($options['search']) && isset($options['search']['schValue'])) {
            $schValue = trim($options['search']['schValue']['value']);
            $where .= " AND (um.mobile='{$schValue}' OR um.truename='{$schValue}')";
            unset($options['search']['schValue'],$options['clID']);
        }
        $dataGrid = new DataGrid();

        $sql    = "SELECT cs.*,um.truename,um.username FROM tang_class_student cs
            LEFT JOIN tang_ucenter_member um ON um.id=cs.cs_studentId WHERE cs_classId='{$clID}' $where";
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
