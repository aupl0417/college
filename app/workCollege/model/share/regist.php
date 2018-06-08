<?php

//登录的模块类。
class login extends guest {

    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $basepath = $GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/system/header.html';
        $headHtml = F::readFile($basepath);
        $basepath = $GLOBALS['cfg_basedir'] . $GLOBALS['cfg_templetspath'] . '/system/footer.html';
        $footHtml = F::readFile($basepath);
    }

    function registFirst() {
        $type = isset($this->options['type']) ? $this->options['type'] : '1';
        $exid = isset($this->options['exid']) ? $this->options['exid'] : '0';
        $arr = array(
            '_replace' => array(
                'usertype' => $type,
                'exid' => $exid,
                'header' => $headHtml,
                'foot' => $footHtml
            )
        );
        $htmlname = 'system/register_step_1';
    }

    function registSecond() {
        $exid = isset($this->options['exid']) ? $this->options['exid'] : '0';
        $arr = array(
            '_replace' => array(
                'exid' => $exid,
                'header' => $headHtml,
                'foot' => $footHtml
            )
        );
        $this->setHeadTag('title', '注册优品试用会员 - 填写注册信息'); //组织显示标题
        $htmlname = 'system/register_step_2';
    }

    function registThree() {
        $arr = array(
            '_replace' => array(
                'header' => $headHtml,
                'foot' => $footHtml,
                'nick' => $this->options['nick']
            )
        );
        $this->setHeadTag('title', '注册优品试用会员 - 注册成功'); //组织显示标题
        $htmlname = 'system/register_step_3';
    }

    function run() {
        
        
        
        
        $this->setTemplateData($arr);
        $this->setTemplateFile($htmlname); //设置模板
    }

}
