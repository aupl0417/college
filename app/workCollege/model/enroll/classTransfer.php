<?php class classTransfer extends worker { function __construct($options) {
        parent::__construct($options, [50010503]);
    }

    function run() {
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        
        $data = array(
            'code' => '50010503',
            'id'   => $this->options['id']
        );
        
        $db = new MySql();
        $data['className'] = $db->getField('select cl_name from tang_student_enroll LEFT JOIN tang_class on cl_id=tse_classId where tse_id="' . $data['id'] . '"');
        
        $classList = $db->getAll('select cl_id as id,cl_name as name from tang_class where cl_state IN(0,1) and cl_status=1');//报名中，且通过审核
        $classList = array_column($classList, 'name', 'id');
        $data['classList'] = F::array2Options($classList);
        
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
