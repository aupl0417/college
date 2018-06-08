<?php

class index extends member {

    function run() {
        user::exitUser(); 
        $url = U('college/index/index');
        header('location:' . $url);
    }

}
