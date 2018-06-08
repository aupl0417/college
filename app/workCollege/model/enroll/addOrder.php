<?php

class addOrder extends worker {
    function __construct($options) {
        parent::__construct($options, [50010503]);
    }

    function run() {
        $data = array(
            'code' => '50010503',
            'orderId' => F::getTimeMarkID(),
        );
        
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
