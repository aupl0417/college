<?php
/*=============================================================================
#     FileName: index.php
#         Desc: 
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-10 20:32:27
#      History:
#      Paramer: 
=============================================================================*/

class index extends guest {
    function run() {
		$validate = new validate();
		$validate->type = $this->options['type'];
		$validate->output(1);
		return true;
    }
}
