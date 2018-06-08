<?php
/*=============================================================================
#     FileName: index.json.php
#         Desc: 班级列表
#       Author: Wuyuanhang 
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-11-04 16:27:06
#      History:
=============================================================================*/
class index_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010301]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $where = ' WHERE 1';
        if(isset($options['search']['cl_state']) && 9 == $options['search']['cl_state']['value']){
            unset($options['search']['cl_state']);
        }

        $sql = "SELECT cl.*,br.br_name FROM tang_class cl LEFT JOIN tang_branch br ON cl.tangCollege=br.br_id $where AND cl_status=1";
        $result   = $dataGrid->create($options,$sql);

        if (!$result['data']) {
            $emptyInfo = array(
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            );

            die(json_encode($emptyInfo));
        }

        //状态 -1 取消 0 报名中 1 开课中 2 结束
        $stateList = array(-1=>'已经取消','报名中','开课中','结束');
        $classList = array(-1=>'font-red-thunderbird','font-blue','font-green-jungle','font-blue');
        $opStr  = "<a href='/classManage/%s?_ajax=1&clID=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $op  = "<a href='/classManage/%s?clID=%d' class='btn-xs blue ajaxify'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';

        $db = new MySql();

        foreach ($result['data'] as &$v) {
            $v['state'] = sprintf($stateStr,$classList[$v['cl_state']],$stateList[$v['cl_state']]);
            $v['DT_RowId']   = 'row_'.$v['cl_id'];

            //报名情况
            $studentNum = $db->count('tang_class_student',"cs_classId='{$v['cl_id']}'");
            $v['enrollNum'] = $studentNum.'/'.$v['cl_allowableNumber'];

            $v['op'] = str_replace('classManage','enrollManage',sprintf(str_replace('clID','id',$opStr),'viewEnroll',$v['cl_id'],'#formModal','fa-search','班级详情')).
            //sprintf($opStr,'editClass',$v['cl_id'],'#formModal','fa-search','班级二维码').
            sprintf($opStr,'schedule',$v['cl_id'],'#formModal','fa-search','排课表').
            sprintf($opStr,'studentList',$v['cl_id'],'#formModal','fa-users','学员列表').
            sprintf($opStr,'studentScoreList',$v['cl_id'],'#formModal','fa-search','成绩表');
            //sprintf($opStr,'addInform',$v['cl_id'],'#formModal','fa-edit','发布通知').
            //sprintf($opStr,'editClass',$v['cl_id'],'#formModal','fa-edit','编辑内容').

            if ($studentNum < $v['cl_allowableNumber']) {
               $v['op'] .= sprintf($opStr,'addStudent',$v['cl_id'],'#formModal','fa-edit','增加学员');
            }

            $doStr = "<a href='javascript:void(0)' class='btn-xs blue %s changeState' data-state='%d' data-name='{$v['cl_name']}' data-id='{$v['cl_id']}'>
                <i class='fa fa-trash'></i> %s</a>";

            if (1 == $v['cl_state']) {
                $v['op'] .= sprintf($opStr,'studentAttendance',$v['cl_id'],'#formModal','fa-search','签到表')
                    .sprintf($doStr,'changeClassState',2,'结束课程');
            }

            if (0 == $v['cl_state']) {
                $v['op'] .= sprintf($doStr,'changeClassState',1,'确定开课')
                    .sprintf($doStr,'changeClassState',-1,'取消开课')
                    .sprintf($opStr,'delayClass',$v['cl_id'],'#formModal','fa-edit','延时开课');
            }
            
            $v['op'] .= sprintf($op,'glimpse',$v['cl_id'],'fa-edit','学员风采');
        }
        echo json_encode($result);
    }
}
