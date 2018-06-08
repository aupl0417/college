<?php
/*
*     FileName: index.php
*         Desc: 课程审核列表
* */
class index extends worker {
    function __construct($options) {
        parent::__construct($options, [50010302]);
    }

    function run() {
        $data = array(
            'code' => '50010302',
        );

        $db = new MySql();
        $branchList         = $db->getAll('SELECT br_id,br_name FROM tang_branch WHERE 1');
        $branchList         = array_column($branchList,'br_name','br_id');
		
        $data['branchList'] = F::array2Options($branchList);
        $data['statusList'] =  F::array2Options(array('未审核','审核通过','审核拒绝'));
		
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
