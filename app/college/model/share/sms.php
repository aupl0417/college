<?php

//短信模块类。
class sms extends guest {

    function run() {
        $this->setTempAndData();
        $this->show();
    }

}
