<?php

class login_json extends guest {

    function run() {
        $db = new MySql();
        $user = new user();
        $cache = new cache();
        $ip = md5(F::GetIP());

        $result = $user->checkUser($this->options['username'], F::getSuperMD5($this->options['password']));

        switch ($result) {
            case '2100':
                $url = 'http://'.INSIDEAPI.'/work/interfaceList'; //'https://mem.a.com'
                $_SESSION['backUrl'] = '';
                if (preg_match("/^http\:\/\/([a-zA-Z]+\.)[\w-]+\.com[\/]?$/",$url) || preg_match("/\/logout/",$url)){//如果从其他项目的首页跳过来登录的，就返回mem的首页
                    $callbak['url'] = 'http://'.INSIDEAPI.'/work/interfaceList';
                }else{
                    $callbak['url'] = $url;    //不是首页则跳回原来的页面
                }
                $this->show(message::getJsonMsgStruct('2100', $callbak)); //登录成功
                break;
            case '2101':
                $url = 'https://'.WORKERURL;
                $this->show(message::getJsonMsgStruct('2101', array('url' => $url))); //员工帐号//跳转
                break;
            case '2106':
                $this->show(message::getJsonMsgStruct('2108')); //登录失败
                break;
            case '2109':
                $url = 'https://'.UCURL.'/resetUsername/';
                $this->show(message::getJsonMsgStruct('2109', array('url' => $url))); //帐号不符合规范
                break;
            default:
                $this->show(message::getJsonMsgStruct('2199')); //登录失败
                $cache->set($ip,'1',30);
                break;
        }
    }

}