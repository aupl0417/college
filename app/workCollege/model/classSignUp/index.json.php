<?php
class index_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010302]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();

        $where = ' WHERE 1';
        if (isset($options['search'])) {
            $search = $options['search'];
        }

        $sql = "SELECT cl_id,cl_name as className,cl_number,cl_enrollStartTime as enrollStartTime,cl_status,cl_startTime,cl_endTime,cl_allowableNumber,br.br_name as branchName,username as headMaster FROM tang_class cl LEFT JOIN tang_branch br ON cl.tangCollege=br.br_id LEFT JOIN tang_ucenter_member on id=cl_headmasterId $where";
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

        //状态 -1 取消 0 报名中 1 开课中 2 结束
        $stateList = array(-1=>'已经取消','报名中','开课中','结束');
        $classList = array(-1=>'font-red-thunderbird','font-blue','font-green-jungle','font-blue');
        $opStr  = "<a href='/classSignUp/%s?_ajax=1&id=%d' data-target='%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i>%s</a>";
        $stateStr = '<span class="%s">%s</span>';

        $db = new MySql();

        foreach ($result['data'] as &$v) {
            $v['DT_RowId']   = 'row_'.$v['cl_id'];

            //报名情况
            $studentNum = $db->count('tang_class_student',"cs_classId='{$v['cl_id']}'");
            $v['enrollNum'] = $studentNum.'/'.$v['cl_allowableNumber'];

            $v['op'] = sprintf($opStr,'detail',$v['cl_id'],'#formModal','fa-search','详情');

            if (0 == $v['cl_status']) {
                $v['op'] .= sprintf($opStr,'review',$v['cl_id'],'#formModal','fa-search','审核');
            }
            $v['status'] = $v['cl_status'] == 0 ? '审核中' : ($v['cl_status'] == 1 ? '已通过' : '已拒绝');
            $v['timeRange'] = $v['cl_startTime'] . '-' . $v['cl_endTime'];

        }
        echo json_encode($result);
    }
}
