<?php
/*=============================================================================
#     FileName: courseTemplate.json.php
#         Desc: 课程模板列表
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:45:49
#      History:
=============================================================================*/
class courseTemplate_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010402]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $sql      = 'SELECT ctt.*,gr.gr_name FROM tang_class_table_templet ctt LEFT JOIN tang_grade gr ON gr.gr_id=ctt.ctt_gradeID WHERE 1';
        $result   = $dataGrid->create($this->options,$sql);

        if (!$result['data']) {
            $emptyInfo = array(
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            );

            die(json_encode($emptyInfo));
        }

        $stateList = array(-1=>'失效',1=>'有效');
        $classList = array(-1=>'font-red-thunderbird',1=>'font-green-jungle');
        $opStr  = "<a href='/courseManage/%s?_ajax=1&id=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';

        foreach ($result['data'] as &$v) {
            $v['state'] = sprintf($stateStr,$classList[$v['ctt_state']],$stateList[$v['ctt_state']]);
            $v['DT_RowId']   = 'row_'.$v['ctt_id'];

            $v['op'] = sprintf($opStr,'viewCourseTemplate',$v['ctt_id'],'#formModal','fa-search','查看课程');
            //$v['op'] .= sprintf($opStr,'editCourseTemplate',$v['ctt_id'],'#formModal','fa-edit','编辑');
            $v['op'] .= "<a href='/courseManage/editCourseTemplate?id={$v['ctt_id']}' class='btn-xs blue ajaxify'><i class='fa fa-edit'></i>编辑</a>"
            ."<a href='javascript:void(0)' class='btn-xs blue delCourseTemp' data-id='{$v['ctt_id']}' data-name='{$v['ctt_name']}'><i class='fa fa-trash'></i> 删除</a>";
        }
        echo json_encode($result);
    }
}
