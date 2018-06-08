<?php

class checkApplication extends guest {
    function __construct($options) {        		
        parent::__construct($options, [8]);
    }

    function run() {

        $data['da_id'] = $this->options['id'];
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

}
