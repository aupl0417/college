<?php

//退出的模块类。
class logout extends guest {

    function run() {
        /*
        @session_start();
        users::exitUser();


        //header("location:?model=login");
        //return "<script>window.location.href = '?model=login&do=loginsys&t={$this->options['do']}';</script>";
        if ($this->options['do'] == 'logout') {
            //return message::show(message::getMessage(1029),'?model=items',0,5000);
            return "<script>window.location.href = '/';</script>";
        }
        if ($this->options['do'] == 'ajax_out') {
            $result = json_encode(message::getMsgStruct('1029'));
            return 'jsonpCb'."($result)";
        }
        return message::show(message::getMessage(1029), "/", 0, 5000);
         * 
         */

            session_start();
            user::exitUser();
            return header('Location:'.$_SERVER['HTTP_REFERER']);
    }

}

?>