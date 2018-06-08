<?php

//登录的模块类。
class login extends guest {

    function quick() {
        $htmlname = 'system/quick_login';
    }

    function mini() {
        $from = isset($this->options['from']) ? $this->options['from'] : '';
        $arr['_replace']['from'] = '&from=' . $from;
        $htmlname = 'system/login_mini';
    }

    function index() {
        $url = ($this->options['rurl'] == '') ? '' : '&rurl=' . urlencode($this->options['rurl']);
        //考虑当get没有rl时 获取点击 来路
        if (!$url && isset($_SERVER["HTTP_REFERER"])) {
            $fromurl = $_SERVER["HTTP_REFERER"];
            if (strstr($fromurl, "?")) {
                $fromurl2 = explode('?', $fromurl);
                $url = urlencode("?" . $fromurl2[1]);
            }
        }
        $refer = isset($fromurl) ? '&from=' . urlencode($fromurl) : '';
        //读取页脚
        $basepath = $GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/system/footer.html';
        $footHtml = F::readFile($basepath);

        $this->setTemplateData(array(
            '_replace' => array(
                'rurl' => $url,
                'foot' => $footHtml,
                'time' => time(),
                'from' => $refer
        )));
        $this->setTemplateFile('system/login');
    }

    //处理相关功能
    function run() {
    }

}
