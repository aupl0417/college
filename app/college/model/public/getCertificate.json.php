<?php

class getCertificate_json extends guest {
    
    function run() {
        $this->db = new MySql();
        $option = $this->options;
        $userType = isset($this->options['userType']) && !empty($this->options['userType']) ? $this->options['userType'] + 0 : 0;
        unset($this->options['type']);
        unset($this->options['userType']);
        
        if(!$_SESSION || !$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }

        $userId = $_SESSION['userID'];
        $uid = $this->db->getField('select id from tang_ucenter_member where userId="' . $userId . '"');

        $dataGrid  = new DataGrid();

        $opStr = '<a href="/%s/?id=%s" class="btn-xs blue"><i class="fa %s"></i> %s</a>';
        $result = array(
            'draw'            => 0,
            'recordsTotal'    => 0,
            'recordsFiltered' => 0,
            'data'            => [],
        );
        
        $sql = 'select tce_id as DT_RowId,tce_id as id,tce_name as certName,tce_url as url,tce_createTime as createTime from tang_certificate where tce_userId="' . $uid . '"';
        if($userType == 0){
            $sql .= ' and tce_userType=0';
        }
        
        $result = $dataGrid->create($this->options, $sql);
        if($result['data']){
            foreach($result['data'] as $key=>&$val){
                $val['op'] = '<a href="' . TFS_APIURL . '/' . $val['url'] . '" target="_blank" class="btn-xs blue"><i class="fa %s"></i> 查看证书</a>'
                            .'<a href="/public/download?id=' . $val['DT_RowId'] . '" class="btn-xs blue"><i class="fa %s"></i> 下载证书</a>';
            }
        }

        echo json_encode($result);
    }

}
