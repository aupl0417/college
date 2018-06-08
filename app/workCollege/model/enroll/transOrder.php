<?php

class transOrder extends worker {
    function __construct($options) {
        parent::__construct($options, [50010503]);
    }

    function run() {
        
        $data = array(
            'code' => '50010503',
        );
        
        $db = new MySql();
        $classList = $db->getAll('select cl_id as id, cl_name as name from tang_class where cl_status=1 and cl_status in (0,1)');
        $classList = array_column($classList, 'name', 'id');
        
        $data['classList'] = F::array2Options($classList);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
