<?php
/*=============================================================================
#     FileName: index.php
#         Desc: 课室列表
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-29 10:08:42
#      History:
#      Paramer:
=============================================================================*/
class index extends worker {
    function __construct($options) {
        //parent::__construct($options, [50040101]);
        parent::__construct($options, [500401]);
    }

    function run() {
        $property         = F::getAttrs(5);
        $trainingSiteType = F::getAttrs(6);

        $data = array(
           //'code' => '50040101',
           'code' => '500401',
           'property' => F::array2Options($property),
           'typeList' => F::array2Options($trainingSiteType),
        );


        $db = new MySql();

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
