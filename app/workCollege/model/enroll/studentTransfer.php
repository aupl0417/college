<?php

class studentTransfer extends worker {
    function __construct($options) {
        parent::__construct($options, [50010503]);
    }

    function run() {
        (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        
        $data = array(
            'code' => '50010503',
            'id'   => $this->options['id']
        );
        
        $db = new MySql();
        $data['username'] = $db->getField('select tse_userTrueName from tang_student_enroll where tse_id="' . $data['id'] . '"');
        
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
