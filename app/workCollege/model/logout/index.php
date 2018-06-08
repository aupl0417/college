<?php

class index extends worker {

    function run() {
        user::exitUser();
        header('location:https://work.dttx.com');// . WORKERURL
    }

}
