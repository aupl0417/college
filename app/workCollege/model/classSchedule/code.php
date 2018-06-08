<?php
/*=============================================================================
#     FileName: code.php
#         Desc: 二维码
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:49:09
#      History:
#      Paramer: 
=============================================================================*/
class code extends guest{
    function run() {
        $options = $this->options;
        $code = new MyQRCode();
        $data = 'tangcollegetangcollegetangcollege_'.$options['id'];
            //.'#'.strtotime('now').str_repeat('#'.F::getGID(),2);
        $code->getOutHtml($data,'L',20);
    }
}
