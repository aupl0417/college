<?php

class attendList_json extends member {

    function __construct($options) {
        parent::__construct($options, [50102]);
        $this->db = new MySql();
    }

    function run() {
        $option = $this->options;
        if(!$_SESSION['userID']){
            header('Location:' . U('college/index/index'));
        }
        $id = $option['id'] + 0;
        $userId = $_SESSION['userID'];
//        $userId = 'aaa263f7936899d2ba78ab57d7a06844';
        $uid = $this->db->getField('select id from tang_ucenter_member where userId="' . $userId . '"');

        $dataGrid  = new DataGrid();
        $attendDateList = $this->db->getAll("select DATE_FORMAT(att_createTime, '%Y-%m-%d') as attendDate from tang_attendance where att_userId='" . $uid . "' and att_classId='" . $id . "'");
        $attendDateList = array_unique(array_column($attendDateList, 'attendDate'));

        $field = 'att_id as DT_RowId,DATE_FORMAT(att_createTime, "%Y-%m-%d") as attendDate,att_createTime as createTime,att_state as state';
        $sql = "select " . $field . " from tang_attendance
                where att_userId='" . $uid . "' and att_classId='" . $id . "'";
        $result = $dataGrid->create($this->options, $sql);

        if($result['data']){
            $dateArr = array_column($result['data'], 'attendDate');
            $dateArr = array_unique($dateArr);
            foreach($dateArr as $k=>$v){
                foreach($result['data'] as $key=>&$val){
                    if($val['attendDate'] == $v){
                        $data[$k]['DT_RowId'] = $v;
                        $data[$k]['attendDate'] = $v;
                        $data[$k]['attendDetail'] .= date('H:i', strtotime($val['createTime'])) . ' | ';
                    }
                }
                $data[$k]['attendDetail'] = trim($data[$k]['attendDetail'], ' | ');
            }
            $result['data'] = $data;
        }

        echo json_encode($result);
    }

}
