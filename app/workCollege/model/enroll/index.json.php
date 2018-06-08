<?php

class index_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010503]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $where = ' WHERE 1 ';
        if (isset($options['search'])) {
            unset($options['search']['act']);
        }

        $field = 'tse_id,username,tse_classId,tse_fee as fee,tse_payFee as payFee,tse_province as province,tse_state,tse_status,tse_createTime,cl_name as className,cl_state,tse_team,cl_number';
        $sql = "SELECT {$field} FROM tang_student_enroll LEFT JOIN tang_class ON cl_id=tse_classId left join tang_ucenter_member on tse_userId=id $where";
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
        
        $statusList = array(-1=>'关闭','未付款','已付款', '已报到', '已转让');
        $stateList = array(-1=>'未通过','待审核','通过');
        
        $classList = array(-1=>'font-red-thunderbird','font-red-thunderbird','font-green-jungle','font-blue', 'font-red-thunderbird');
        $opStr  = "<a href='/enroll/%s?_ajax=1&id=%s' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';
        
        $db = new MySql();
        foreach ($result['data'] as &$v) {
            $v['tse_team'] = empty($v['tse_team']) ? '<span class="font-red-thunderbird">未分组</span>' : "第{$v['tse_team']}组";
            $v['op'] = sprintf($opStr, 'viewEnroll', $v['tse_id'], '#formModal', 'fa-search', '查看')
                .sprintf($opStr,'editTeam',$v['tse_id'],'#formModal','fa-edit','分组');
            if($v['cl_state'] == 1 && $v['tse_status'] == 0){
                $db->update('tang_student_enroll', array('tse_status' => -1), 'tse_id="' . $v['tse_id'] . '"');
                $v['tse_status'] = -1;
            }
            
            $v['state'] = sprintf($stateStr,  $classList[$v['tse_state']],  $stateList[$v['tse_state']]);
            $v['status'] = sprintf($stateStr, $classList[$v['tse_status']], $statusList[$v['tse_status']]);
            $v['DT_RowId']   = 'row_'.$v['tse_id'];
            $v['tid']   = '<input type="checkbox" name="ids[]" value="' . $v['tse_id'] . '"/>';
            
            if($v['tse_state'] == 0 &&  !in_array($v['tse_status'], array(-1, 3))){
                $v['op'] .= sprintf($opStr, 'review', $v['tse_id'], '#formModal', 'fa-edit', '审核');
            }
            
            if ($v['tse_status']  == 1) {
                $v['op'] .= //sprintf($opStr, 'studentTransfer', $v['tse_id'], '#formModal', 'fa-edit', '转人').
                            sprintf($opStr, 'classTransfer', $v['tse_id'], '#formModal', 'fa-edit', '转班');
            }
            
            if($v['tse_state'] != -1 && $v['tse_status'] != -1){
                $v['op'] .= "<a href='javascript:void(0)' class='btn-xs blue delOrder' data-id='{$v['tse_id']}'><i class='fa fa-trash'></i> 关闭</a>";
            }
            
        }
        
        echo json_encode($result);
    }
}
