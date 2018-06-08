<?php
/*=============================================================================
#     FileName: index.json.php
#         Desc: 排课列表
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-25 17:49:18
#      History:
=============================================================================*/
class index_json extends worker{
    public function __construct($options){
        parent::__construct($options,[500102]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $where = ' WHERE 1 ';
        //if (!isset($options['search'])) {
        //    $where .= " AND cta.cta_startTime>'".date('Y-m-d 00:00:00')."' ";
        //}

        if (isset($options['clID']) && !empty($options['clID'])) {
            $where .= " AND cta.cta_classId={$options['clID']}";
        }

        $sql = "SELECT cta.*,um.truename teacherName,co.co_name,tra.tra_name,cl.cl_name FROM tang_class_table cta LEFT JOIN tang_class cl ON cl.cl_id=cta.cta_classId
            LEFT JOIN tang_course co ON cta.cta_courseId=co.co_id
            LEFT JOIN tang_ucenter_member um ON um.id=cta.cta_teacherId
            LEFT JOIN tang_trainingsite tra ON cta.cta_trainingsiteId=tra.tra_id $where";

        $result = $dataGrid->create($this->options,$sql);

        if (!$result['data']) {
            $emptyInfo = array(
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            );

            die(json_encode($emptyInfo));
        }

        $classList = array(-1=>'font-red-thunderbird','font-red-thunderbird','font-green-jungle','font-blue');
        $opStr  = "<a href='/%s/%s?_ajax=1&id=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';

        foreach ($result['data'] as &$v) {
            $v['DT_RowId']   = 'row_'.$v['cta_id'];
            $v['op'] = sprintf($opStr,'courseManage','viewCourse',$v['cta_courseId'],'#formModal','fa-search','课程详情')
                .sprintf($opStr,'classSchedule','viewQcode',$v['cta_id'],'#formModal','fa-edit','二维码')
                .sprintf($opStr,'classSchedule','modCourseSchedule',$v['cta_id'],'#formModal','fa-edit','临时调课');

            if (strtotime("now") < strtotime($v['cta_startTime'])) {
                $v['op'] .= "<a href='javascript:void(0)' class='btn-xs blue delSchedule' data-name='{$v['co_name']}' data-id='{$v['cta_id']}'><i class='fa fa-trash'></i> 删除</a>";
            }

        }
        echo json_encode($result);
    }
}
