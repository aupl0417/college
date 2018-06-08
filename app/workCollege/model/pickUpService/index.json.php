<?php

class index_json extends worker{
    public function __construct($options){
        parent::__construct($options,[50010505]);
    }

    public function run(){
        $options = $this->options;
        $dataGrid = new DataGrid();
        
        $where = ' WHERE (tse_station<>"" && tse_arrivalTime<>"" && tse_counts<>0) and tse_state=1 and tse_status<>-1';
        if (isset($options['search'])) {
            unset($options['search']['act']); 
        }
        
        $field = 'tse_id,tse_classId,tse_createTime,tse_arrivalTime,tse_station,tse_counts,username,trueName,mobile,cl_name as className';
        $sql = "SELECT {$field} FROM tang_student_enroll LEFT JOIN tang_ucenter_member on tse_userId=id
                LEFT JOIN tang_class on tse_classId=cl_id $where";
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
        
        $db = new MySql();
        foreach ($result['data'] as &$v) {
            $v['DT_RowId']   = 'row_'.$v['tse_id'];
//             $v['tse_arrivalTime'] = $this->getArrivalTime($v['tse_arrivalTime']);
        }
        
        $result['data'] = $this->sortResult($result['data'], 'tse_arrivalTime', 'desc');
        
        echo json_encode($result);
    }
    
    private function getArrivalTime($arrivalTime){
        if($arrivalTime == '0000-00-00 00:00:00'){
            return '';
        }
        
        $arrivalTime = date('Y年m月d日 H:i:s', strtotime($arrivalTime));
        
        return $arrivalTime;
    }
    
    //由于有了查询条件，加排序 会导致出错，所以用这个方法来排序
    private function sortResult($result, $order = 'tse_id', $sort = 'desc'){
        if(empty($result)){
            return array();
        }
        
        $tIds = array_column($result, $order);
        if(strtolower($sort) == 'desc'){
            rsort($tIds);
        }else{
            asort($tIds);
        }
        
        foreach($tIds as $key=>$val){
            foreach ($result as $k=>&$v){
                if($val == $v[$order]){
                    $v['tse_arrivalTime'] = $this->getArrivalTime($v['tse_arrivalTime']);
                    $data[] = $result[$k];
                }else {
                    continue;
                }
            }
        }
        
        return $data;
    }
}
