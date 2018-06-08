<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/7
 * Time: 18:58
 */
class denglu extends guest{
    function run(){
        $this->setTempAndData();
        $this->show();
    }
}