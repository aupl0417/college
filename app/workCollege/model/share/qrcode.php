<?php

//校验图片的模块类。
class qrcode extends guest {

    function run() {
        $web_sessionID = ($this->options["web_sessionID"]) ? ($this->options["web_sessionID"]) :'';

        $value = "http://xx.xx.xxx/loginByQRcode/" . $web_sessionID;
        $errorCorrectionLevel = "L";
        $matrixPointSize = "6";
        return MyQRCode::getOutHtml($value, $errorCorrectionLevel, $matrixPointSize);

        /*         * ************************************************
         * 
         * 以下用于向二维码图片中加入一张logo
         * 
         * ************************************************ */

        /*
          $logo = 'logo.png';//准备好的logo图片
          $QR = "qrcode.png";//已经生成的原始二维码图

          if ($logo !== FALSE) {
          $QR = imagecreatefromstring(file_get_contents($QR));
          $logo = imagecreatefromstring(file_get_contents($logo));
          $QR_width = imagesx($QR);//二维码图片宽度
          $QR_height = imagesy($QR);//二维码图片高度
          $logo_width = imagesx($logo);//logo图片宽度
          $logo_height = imagesy($logo);//logo图片高度
          $logo_qr_width = $QR_width / 5;
          $scale = $logo_width/$logo_qr_width;
          $logo_qr_height = $logo_height/$scale;
          $from_width = ($QR_width - $logo_qr_width) / 2;
          //重新组合图片并调整大小
          imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
          $logo_qr_height, $logo_width, $logo_height);
          }
          //输出图片
          imagepng($QR, 'QRcode.png');
          echo "<img src = 'QRcode.png'/>"; */
    }

}
