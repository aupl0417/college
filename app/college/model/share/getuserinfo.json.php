<?php

//登录的模块类。
class getuserinfo_json extends guest {

    function run() {
        //页面初始化时，ajax方式获取用户状态
        //读取session中的用户，当用户不存在时，返回‘游客’，否则返回用户nick；
        //后期升级时，可以返回信件信息、买\卖家个人信息、个人最新记录、最新更新信息等	
        $us = new users();
        //读取用户的id
        $id = $us->getUserAttrib('userID');
        if ($id == '') {
            $nick = '';
            $usertype = 'guest';
            $logimg = 'guest';
            $score = 0;
        } else {
            //读取用户的nick
            $nick = $us->getUserAttrib('userNick');
            //读取用户的身份标识
            $usertype = $us->getUserAttrib('userType');
            //读取用户的头像
            $logimg = $GLOBALS['cfg_photos'] . "/" . $id . ".jpg";
            //获取积分
            $score = $us->getUserByID($id, 'ac_score');
            $score = $score['ac_score'];
        }
        $arr = array(
            'nick' => $nick,
            'usertype' => $usertype,
            'logimg' => $logimg,
            'score' => $score
        );
        return json_encode($arr);
    }

}
