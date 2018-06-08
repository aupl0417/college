<?php
/*=============================================================================
#     FileName: attendance.php
#         Desc: 返回课程签到信息
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-25 17:48:59
#      History:
#      Paramer: 
=============================================================================*/
class attendance extends guest {
    function run(){
        echo 'tangcollege_'.$this->options['id'];;
    }
}
