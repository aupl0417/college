<?php

class index extends worker {
    function __construct($options) {
        parent::__construct($options, [50010503]);
    }

    function run() {
        $data = array(
            'code' => '50010503',
        );
        $db = new MySql();
        $provinceList = $db->getAll("select a_id as id, a_name as name from tang_area where a_fkey=0");
        $provinceList = array_column($provinceList, 'name','name');
        $data['provinceList'] =  F::array2Options($provinceList);
        $data['statusList']   =  F::array2Options(array(-1=>'已关闭','未付款','已付款','已使用','已转人'));
        $data['stateList']    =  F::array2Options(array(-1=>'未通过','待审核','通过'));

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
