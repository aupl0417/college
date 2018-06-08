<?php

class index extends guest {

    function __construct($options) {        		
        parent::__construct($options, [60110]);			
    }
    function run() {
		header('Location:work/interfaceList');
    }
}
