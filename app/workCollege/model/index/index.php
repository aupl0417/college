<?php
/*=============================================================================
#     FileName: index.php
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-15 16:25:06
#      History:
#      Paramer:
=============================================================================*/
class index extends worker {
    function __construct($options) {
        parent::__construct($options, [50]);
    }
    function run() {
        //$_SESSION['TCPower'] = '0';
        ////if(isset($_SESSION['fromERP']) && $_SESSION['fromERP'] == '1'){
        ////    if(isset($_SESSION['TCPower']) && $_SESSION['TCPower'] == '1'){
        ////        //已经读取了唐人大学的附加权限
        ////    }else{
        ////    }
        ////    //读取唐人大学的附加权限
        ////}

        if(!$this->getTCPower()){
           $this->setReplaceData(['error' => $this->error]);
           $this->setTempAndData('error');
           $this->show();
           exit();
        };

        $return = isset($this->options['return']) ? urldecode($this->options['return']) : '';
        $root   = isset($this->options['root']) ? ($this->options['root'] - 0) : 0;

        if ($return != '') {
            $this->setReplaceData(array('reUrl' => $return, 'reRoot' => $root));
            $this->setTempAndData('blank');
        } else {
            $this->setReplaceData($_SESSION);
            $this->setReplaceData([]);
            $this->setTempAndData();
        }
        $this->show();
    }

    private function getTCPower(){
        $db = new MySql();
        $sql = "SELECT * FROM `tang_employee` WHERE e_id='".$_SESSION['userID']."'";
        $result = $db->getRow($sql);

        if(!$result){
            $this->error = '未被授权进入该系统!';
            return false;
        }
        else{
            $powerList = $result['e_powerList'];
            $powerHash = $result['e_powerHash'];
            //echo F::powerHash($powerList);die;
            if($powerHash != F::powerHash($powerList)){
                //echo $powerHash .' !='.  F::powerHash($powerList);die;
                $this->error = '权限被篡改!!';
                return false;
            }
            else{
                $_SESSION['userPower'] .= ','.$result['e_powerList'];
                $_SESSION['TCPower'] = '1';
                return true;
            };
        }
    }
}
