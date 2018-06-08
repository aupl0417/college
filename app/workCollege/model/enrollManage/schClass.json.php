<?php
/*=============================================================================
#     FileName: schClass.json.php
#         Desc: 查询班级
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-11-08 15:03:22
#      History:
=============================================================================*/
class schClass_json extends worker{
    function __construct($options) {
        parent::__construct($options, [500105]);
    }

    public function run(){
        $options  = $this->options;
        $dataGrid = new DataGrid();

        if (!isset($options['search']['classId']) || empty($options['search']['classId']['value'])) {
            die(F::dtEmpty());
        }

        $classId = trim($options['search']['classId']['value']);
        unset($options['search']);
        $where = " WHERE cl_id='{$classId}'";

        $sql    = "SELECT cl_name,cl_id,cl_startTime FROM tang_class $where";
        $result = $dataGrid->create($options,$sql);

        if (!$result['data']) {
            die(F::dtEmpty());
        }

        $opStr  = "<a href='/%s/%s?_ajax=1&clID=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i data-clid='%d' class='fa %s'></i>%s</a>";

        $db = new MySql();
        foreach ($result['data'] as &$v) {
            $v['DT_RowId']   = 'row_'.$v['cl_id'];
            $v['reportNum'] = $db->count('tang_class_student',"cs_classId='{$classId}' AND cs_status=0");
            $v['op'] = sprintf(str_replace('clID','id',$opStr),'enrollManage','viewEnroll',$v['cl_id'],'#formModal',$v['cl_id'],'fa-search','班级详情')
             .sprintf($opStr,'enrollManage','viewQcode',$v['cl_id'],'#formModal','','fa-edit','报到二维码')
             .sprintf($opStr,'enrollManage','teamArrivalInfo',$v['cl_id'],'#formModal','','fa-edit','分组情况');
        }
        echo json_encode($result);
    }
}
