<?php
/*=============================================================================
#     FileName: studentList.json.php
#         Desc: 班级学员列表
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:47:02
#      History:
=============================================================================*/
class studentList_json extends worker{
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

        $clID = $options['clID'];
        $where = '';
        if (isset($options['search']) && isset($options['search']['schValue'])) {
            $schValue = trim($options['search']['schValue']['value']);
            $where .= " AND (um.mobile='{$schValue}' OR um.username='{$schValue}')";
            unset($options['search']['schValue'],$options['clID']);
        }
        $dataGrid = new DataGrid();

        $sql    = "SELECT cs.*,um.mobile,um.truename,um.username FROM tang_class_student cs
            LEFT JOIN tang_ucenter_member um ON um.id=cs.cs_studentId WHERE cs_classId='{$clID}' $where";
        $result = $dataGrid->create($this->options,$sql);

        if (!$result['data']) {
            die(json_encode($emptyInfo));
        }

        foreach ($result['data'] as &$v) {
            $v['DT_RowId']   = 'row_'.$v['cs_id'];
            $v['op'] = "<a href='javascript:void(0)' class='btn-xs blue delStudent' data-name='{$v['username']}' data-clid='{$v['cs_classId']}'
                data-id='{$v['cs_id']}'><i class='fa fa-trash'></i> 删除</a>";
        }
        echo json_encode($result);
    }
}
