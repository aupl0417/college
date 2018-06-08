<?php
/*=============================================================================
#     FileName: code.php
#         Desc: 二维码
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-15 08:40:16
#      History:
#      Paramer: 
=============================================================================*/
class code extends guest{
    function run() {
        $data = $this->options;
        $url = urldecode($data['u']);
        $code = new MyQRCode();
        $result = $code->getOutHtml($url,'L',20);
    }
}
