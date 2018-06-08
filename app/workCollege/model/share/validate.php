<?php

//校验图片的模块类。
class imgcode extends guest {

    function run() {
        $temp = new validate();
        return $temp->getValidateImage();
    }

}
