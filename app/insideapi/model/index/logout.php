<?php

class logout extends guest {

    function run() {
        user::exitUser();
        header('location:http://' . INSIDEAPI);
    }

}
