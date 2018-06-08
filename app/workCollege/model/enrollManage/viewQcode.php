<?php
/*=============================================================================
#     FileName: viewQcode.php
#         Desc: 学员报到二维码
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-15 16:53:39
#      History:
#      Paramer:
=============================================================================*/
class viewQcode extends worker {
    function run() {
        $options = $this->options;

        if (isset($options['act']) && 'view' ==$options['act']) {
            die($this->view($options['clID']));
        }

        $data = array(
            'code' => '500102',
             'id' => intval($options['clID']),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }

    private function view($id){
        $code = new MyQRCode();
        $data = 'tangcollegetangcollege_'.$id.'-'.$_SESSION['userID'];
        $code->getOutHtml($data,'L',20);
    }
}
