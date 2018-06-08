<?php
/*=============================================================================
#     FileName: reportStudent.json.php
#         Desc: 已经报到的学员
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-11-14 15:05:36
#      History:
=============================================================================*/
class reportStudent_json extends worker{
    function __construct($options) {
        parent::__construct($options, [500105]);
    }

    public function run(){
        $options  = $this->options;

        if (!isset($options['search'])) {
            die(F::dtEmpty());
        }

        $search = &$options['search'];
        $where = '';

        if (isset($search['tse_classId']) && !empty($search['tse_classId']['value'])) {
            $clID = $search['tse_classId']['value'];
        }else{
            $clID = $search['clID']['value'];
        }

        unset($options['search']['clID'],$options['search']['act']);

        if (isset($search['studentName']) && !empty($search['studentName']['value'])) {
            $where .= " AND um.trueName LIKE '{$search['studentName']['value']}%'";
            unset($options['search']['studentName']);
        }

        if (isset($search['mobile']) && !empty($search['mobile']['value'])) {
            $where .= " AND um.mobile LIKE '{$search['mobile']['value']}%'";
            unset($options['search']['mobile']);
        }

        if (isset($search['certNum']) && !empty($search['certNum']['value'])) {
            $where .= " AND um.certNum LIKE '{$search['certNum']['value']}%'";
            unset($options['search']['certNum']);
        }

        if (isset($search['team'])) {
            if (0 == $search['team']['value']) {
                $where .= " AND (tse_team=0 OR tse_team IS NULL)";
            }else{
                $where .= " AND tse_team='{$search['team']['value']}'";
            }
            unset($options['search']['team']);
        }

        if (isset($search['hasCertNum']) && !empty($search['hasCertNum']['value'])) {
            if (1 == $search['hasCertNum']['value']) {
                $where .= " AND um.certNum<>''";
            }elseif(-1 == $search['hasCertNum']['value']){
                $where .= " AND (um.certNum IS NULL OR um.certNum='')";
            }

            unset($options['search']['hasCertNum']);
        }

        $dataGrid = new DataGrid();

        $sql = "SELECT cs.cs_id,cs.cs_createTime,cs.cs_studentId,cs.cs_team,cs.cs_status,um.mobile,um.truename,
            um.username,um.certNum,tse.tse_id,tse.tse_province as province,tse.tse_createTime,tse_team,tse.tse_state,tse.tse_status,tse.tse_userId
            FROM tang_student_enroll tse LEFT JOIN tang_ucenter_member um ON um.id=tse.tse_userId
            LEFT JOIN tang_class_student cs ON cs.cs_studentId=tse.tse_userId AND cs.cs_classId=tse.tse_classId
            WHERE tse_classId='{$clID}' $where AND tse_state=1 AND tse_status IN(1,2)";

        $result = $dataGrid->create($options,$sql);

        if (!$result['data']) {
            die(F::dtEmpty());
        }

        $opStr  = "<a href='/%s/%s?_ajax=1&id=%s&clID={$clID}' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $styleList = array(1=>'font-red-thunderbird','font-green-jungle');
        $stateStr = "<span class='%s'>%s</span>";
        $reportState = array(1=>'未报到','已报到');

        foreach ($result['data'] as &$v) {
            $v['DT_RowId'] = 'row_'.$v['tse_id'];
            //$v['certNum']  = F::hidIDCnum($v['certNum']);
            $v['certNum']  = empty($v['certNum']) ? "<span class='{$styleList[1]}'>无身份证号</span>" : substr_replace($v['certNum'],'*******',6,8);
            $v['mobile']   = F::hidtel($v['mobile']);
            $v['report_status'] = sprintf($stateStr,$styleList[$v['tse_status']],$reportState[$v['tse_status']]);
            //$v['cs_createTime'] = empty($v['cs_createTime']) ? "<span class='{$styleList['-1']}'>未报到</span>" : $v['cs_createTime']; 
            $v['cs_createTime'] = empty($v['cs_createTime']) ? '-' : $v['cs_createTime']; 

            $v['certNum'] = empty($v['certNum']) ? sprintf($stateStr,'font-red-thunderbird','无身份证') : $v['certNum'];
            $v['team'] = empty($v['tse_team']) ? '<span class="font-red-thunderbird">未分组</span>' : "第{$v['tse_team']}组";
            $v['op'] = sprintf($opStr,'enroll','viewEnroll',$v['tse_id'],'#formModal','fa-search','报名信息')
                .sprintf($opStr,'student','studentDetail',$v['tse_userId'],'#formModal','fa-search','学员信息')
                .sprintf($opStr,'enrollManage','editTeam',$v['tse_id'],'#formModal','fa-edit','分组');
                //."<a href='javascript:void(0)' class='btn-xs blue delStudent' data-name='{$v['username']}' data-clid='{$clID}'
                //data-id='{$v['cs_id']}'><i class='fa fa-trash'></i> 删除</a>";

            if ($v['tse_status'] == 1 && $v['tse_state'] == 1) {
                $v['op'] .= sprintf($opStr,'enrollManage','getStudentArrivalInfo',$v['tse_userId'],'#formModal','fa-search','确认报到');
            }

            if (2 == $v['tse_status']) {
                //$v['op'] .= sprintf($opStr,'enrollManage','viewIdentity',$v['tse_userId'],'#formModal','fa-print','预览学员证');
                $v['op'] .= "<a href='/enrollManage/printIdentity?id={$v['tse_userId']}&clID={$clID}' target='blank' class='btn-xs blue'><i class='fa '></i>打印学员证</a>";
                //$v['op'] .= "<a href='/enrollManage/printIdentity?id={$v['tse_userId']}&clID={$clID}' target='blank' class='btn-xs blue'><i class='fa '></i>打印学员证</a>";
            }
        }
        echo json_encode($result);
    }
}
