<?php

class resourceList_json extends member {

    function __construct($options) {
        parent::__construct($options, [502]);
        $this->db = new MySql();
    }

    function run() {
        $dataGrid  = new DataGrid();
        
        $resourceList = array();
        if(!$_SESSION || !$_SESSION['userID']){
            $resourceList = array(
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
            );
            
            die(json_encode($resourceList));
        }
        
        $field = 'cr_id as DT_RowId,cr_name as name,cr_readCount as readCount,cr_type,cr_createTime as createTime,username,crd_url,co_name as courseName';
        $sql   = 'select ' . $field . ' from `tang_course_resource` 
                 LEFT JOIN tang_ucenter_member on cr_userId=id 
                 LEFT JOIN tang_course on co_id=cr_courseId 
                 LEFT JOIN tang_course_resource_file on crd_resourceId=cr_id 
                 where cr_userId=(select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '") and cr_courseId="' . intval($this->options['id']) . '"';
        $resourceList = $dataGrid->create($this->options, $sql);
        $opStr = '<a href="/teacher/%s/?_ajax=1&id=%s" data-target="%s" data-toggle="modal" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        if($resourceList['data']){
            foreach ($resourceList['data'] as $key=>&$val){
                $val['op'] = sprintf($opStr, 'resourceDetail', $val['DT_RowId'] . '&type=' . $val['cr_type'], '#temp-modal-power', 'fa-edit', '详情');
                if($val['cr_type'] == 0){
                    $val['op'] = sprintf($opStr, 'resourceDetail', $val['DT_RowId'] . '&type=' . $val['cr_type'], '#temp-modal-power', 'fa-edit', '下载');
                }
            }
        }
        
        echo json_encode($resourceList);
    }

}
