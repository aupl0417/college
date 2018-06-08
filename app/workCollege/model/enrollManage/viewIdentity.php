<?php
/*=============================================================================
#     FileName: viewIdentity.php
#         Desc: 打印学员证
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-11-18 13:59:19
#      History:
=============================================================================*/
class viewIdentity extends worker{

    public function run(){
        include_once(FRAMEROOT."/lib/mpdf/mpdf.php");
        $options  = $this->options;

        if (!isset($options['id'],$options['clID']) || empty($options['id']) || empty($options['clID'])) {
            die;
        }

        $db = new MySql();
        $className = $db->getField("SELECT cl_name FROM tang_class WHERE cl_id='{$options['clID']}'");

        $sql = "SELECT trueName,username FROM tang_ucenter_member WHERE id='{$options['id']}'";
        $userInfo = $db->getRow($sql);

        $className = '大唐天下C+ 商业模式培训会（第12期）';
        $reg = '/(\d+)/';
        preg_match_all($reg,$className,$result);

        $info = array('term' => $result[0][0],'trueName'=>$userInfo['trueName']);

        $this->setReplaceData($info);
        $this->setTempAndData();
        $this->show();
        exit;
    }
}
