<?php

class index extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
        $data = [
            '_replace' => [
                'webname' => '首页',
				'curPage' => 0
                //'wwwUrl' => 'http://'WWWURL,
                //'sidebar' => $this->sidebar
            ]
        ];
        $this->setTempAndData('index', $data);
        $this->show();
    }

}
