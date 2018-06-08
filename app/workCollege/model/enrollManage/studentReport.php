<?php
/*=============================================================================
#     FileName: studentReport.php
#         Desc: 学员报到
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-14 15:05:41
#      History:
#      Paramer:
=============================================================================*/
class studentReport extends worker {
    function __construct($options) {
        parent::__construct($options, [500105]);
    }

    function run() {
        $db = new MySql();
        //距离当天长短排序
        //$yestoday = date('Y-m-d',strtotime("-7 day"));
        //$sql = "SELECT cl_id,cl_name FROM tang_class WHERE cl_startTime>'{$yestoday}' AND  cl_status=1 AND cl_state IN(0,1) ORDER BY DATEDIFF(cl_startTime,CURDATE()) ASC";

        //$sql = "SELECT cl_id,cl_name FROM tang_class WHERE cl_status=1 AND cl_state IN(0,1,2) ORDER BY DATEDIFF(cl_startTime,CURDATE()) ASC";
        $sql = "SELECT cl_id,cl_name FROM tang_class WHERE cl_status=1 AND cl_state IN(0,1,2) ORDER BY cl_number DESC";

        $classList = $db->getall($sql);
        $classList = array_column($classList,'cl_name','cl_id');
        $provinceList = $db->getAll("select a_id as id, a_name as name from tang_area where a_fkey=0");
        $provinceList = array_column($provinceList, 'name','name');

        $data = array(
            'code' => '500105',
            'classList' => F::array2Options($classList),
            'provinceList' => F::array2Options($provinceList),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
