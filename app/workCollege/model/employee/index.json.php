<?php

class index_json extends worker {
	function __construct($options) {		
        parent::__construct($options, [500501]);
    }

    function run() {	
        $options  = $this->options;
        $dataGrid = new DataGrid();
        $fields   = 'e_id,e_uid,e_name,e_tel,e_departmentID,e_dutyID,e_logCount,e_joinTime,e_logIp,e_state';
        $where    = ' WHERE 1';
		
        $db = new MySql();
       
		
        $sql = "SELECT $fields FROM tang_employee e  "
		."  $where";
		
        $result = $dataGrid->create($this->options,$sql);
		
        if ($result['data']) {
            $opStr = "<a href='/%s/%s?_ajax=1&id=%s' data-target='#%s' data-toggle='modal' class='btn-xs blue'><i class='fa %s'></i> %s</a>";
           
            foreach ($result['data'] as &$v) {
                $v['DT_RowId'] = 'row_'.$v['e_id'];
                
				$v['op']       = sprintf($opStr,'power','powerEmployee',$v['e_id'],'temp-modal-power','fa-sitemap','权限');
				
                $v['op']       .='<a href="/?return=/employee/history?id='.$v['e_id'].'&root=2" target="_blank"><i class="fa fa-history"></i> 操作日志 </a>';
				
            }
        }
        echo json_encode($result);		
    }
}
