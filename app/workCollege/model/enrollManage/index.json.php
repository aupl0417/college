<?php
/*=============================================================================
#     FileName: index.json.php
#         Desc: 报名记录列表
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-11-17 17:49:55
#      History:
=============================================================================*/
class index_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010303]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $where = ' WHERE cl_status<>-1 ';
        if(isset($options['search']['cl_status']) && 3 == $options['search']['cl_status']['value']){
            unset($options['search']['cl_status']); 
        }

        $sql = "SELECT cl.*,br.br_name FROM tang_class cl LEFT JOIN tang_branch br ON cl.tangCollege=br.br_id $where";
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

        $stateList = array('待审核','通过','不通过');
        $classList = array('font-red-thunderbird','font-green-jungle','font-red-thunderbird');
        $opStr  = "<a href='/%s/%s?_ajax=1&id=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';
        foreach ($result['data'] as &$v) {
            $v['state'] = sprintf($stateStr,$classList[$v['cl_status']],$stateList[$v['cl_status']]);
            $v['DT_RowId']   = 'row_'.$v['cl_id'];

            $v['op'] = sprintf($opStr,'enrollManage','viewEnroll',$v['cl_id'],'#formModal','fa-search','查看')
                .sprintf($opStr,'classManage','editTeam',$v['cl_id'],'#formModal','fa-search','分组');

//             if (in_array($v['cl_status'],array(0,1)) && 0 == $v['cl_state']) {
                $v['op'] .= "<a href='/enrollManage/editEnroll?id={$v['cl_id']}' class='btn-xs blue ajaxify'><i class='fa fa-edit'></i>编辑</a>";
//             }

            if (0 == $v['cl_status']) {
                //$v['op'] .= "<a href='/enrollManage/editEnroll?id={$v['cl_id']}' class='btn-xs blue ajaxify'><i class='fa fa-edit'></i>编辑</a>"
                $v['op'] .="<a href='javascript:void(0)' class='btn-xs blue delClass' data-name='{$v['cl_name']}' data-id='{$v['cl_id']}'><i class='fa fa-trash'></i> 删除</a>";
            }
        }
        echo json_encode($result);
    }
}
