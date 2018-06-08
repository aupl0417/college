<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/16
 * Time: 17:35
 */
class monitor extends worker {
    function __construct($options) {
        parent::__construct($options, [60201]);
    }

    function run() {
        $this->setTempAndData('monitor');
        $this->show();
    }
}