<?php
/*=============================================================================
#     FileName: index.json.php
#         Desc: 课程管理
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:46:38
#      History:
=============================================================================*/
class index_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010401]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $where = ' WHERE 1';
        if (isset($options['search'])) {
            $search = $options['search'];
        }

        $sql = "SELECT tc.*,sd.sd_name co_studyDirectionName,gr.gr_name co_gradeName FROM tang_course tc LEFT JOIN tang_study_direction sd ON tc.co_studyDirectionId=sd.sd_id
            LEFT JOIN tang_grade gr ON gr.gr_id=tc.co_gradeID $where";
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
        $opStr  = "<a href='/courseManage/%s?_ajax=1&coID=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';

        foreach ($result['data'] as &$v) {
            $v['state']    = sprintf($stateStr,$classList[$v['co_state']],$stateList[$v['co_state']]);
            $v['DT_RowId'] = 'row_'.$v['co_id'];
            $v['op']       = sprintf($opStr,'editCourse',$v['co_id'],'#formModal','fa-edit','编辑');
        }
        echo json_encode($result);
    }
}
