<?php
/*=============================================================================
#     FileName: courseDirection.json.php
#         Desc: 课程分类列表
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 20:42:47
#      History:
=============================================================================*/
class courseDirection_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010403]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $sql = 'SELECT * FROM tang_study_direction';
        $result   = $dataGrid->create($this->options,$sql);

        if (!$result['data']) {
            die(F::dtEmpty());
        }

        $stateList = array(-1=>'失效',0=>'有效');
        $classList = array(-1=>'font-red-thunderbird',0=>'font-green-jungle');
        $opStr  = "<a href='/courseManage/%s?_ajax=1&id=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';

        foreach ($result['data'] as &$v) {
            $v['state'] = sprintf($stateStr,$classList[$v['sd_state']],$stateList[$v['sd_state']]);
            $v['DT_RowId'] = 'row_'.$v['sd_id'];
            $v['op']       = sprintf($opStr,'editCourseDirection',$v['sd_id'],'#formModal','fa-edit','编辑');
        }
        echo json_encode($result);
    }
}
