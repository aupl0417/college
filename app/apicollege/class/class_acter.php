<?php

/**
 * 角色类（控制器扩展类）
 * 身份说明：0－无身份；1-会员；2-雇员
 */
//普通访客
abstract class guest extends controller {

    public function __construct($options = [], $power = []) {
        parent::__construct($options, [0, 1, 2], $power);
        $this->head = array_key_exists('_ajax', $this->options) ? '' : F::readFile(APPROOT. '/template/cn/share/header.html');
        $this->foot = array_key_exists('_ajax', $this->options) ? '' : F::readFile(APPROOT. '/template/cn/share/footer.html');
    }

}