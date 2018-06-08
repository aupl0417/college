<?php
/* ajax请求的登录状态 */
class loginStatus extends guest {

    function run() {
        
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){ 
            $u_nick = isset($_SESSION['userNick']) ? $_SESSION['userNick'] : '';
            echo $u_nick;
            exit;
        }
        
    }
}
