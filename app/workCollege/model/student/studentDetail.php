<?php
/*=============================================================================
#     FileName: studentDetail.php
#         Desc: 学生详细信息
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-26 15:19:17
#      History:
#      Paramer:
=============================================================================*/
class studentDetail extends worker {
    function __construct($options) {
        parent::__construct($options, [50020101]);
    }

    function run() {
        $data = array(
            'code'   => '50020101',
            'tempId' => 'temp_' . F::getGID()
        );

        $options = $this->options;
        if (!isset($options['id']) || empty($options['id'])) {
            die('<span class="font-red">学生ID参数错误</span>');
        }

        $db = new MySql();
        $sql = "SELECT um.id,um.username,um.trueName,um.mobile,um.certNum,um.avatar,um.authImage,um.type,br.br_name branchName FROM tang_ucenter_member um
            LEFT JOIN tang_branch br ON um.tangCollege=br.br_id WHERE id='{$options['id']}'";
        $info = $db->getRow($sql);

        if (empty($info)) {
            die('<span class="font-red">获取学生信息失败</span>');
        }

        //$info['avatar']  = !empty($info['avatar']) ? TFS_APIURL.'/'.$info['avatar'] : _TEMP_PUBLIC_."/images/none.png";
        $info['avatar']    = !empty($info['avatar']) ? $info['avatar'] : _TEMP_PUBLIC_."/images/none.png";
        if (empty($info['authImage'])) {
            $info['authImage']   = [];
            $info['authImage'][] = _TEMP_PUBLIC_."/images/none.png";
        }else{
           $info['authImage'] = unserialize($info['authImage']); 
        }

        $info['mobile']    = F::hidtel($info['mobile']);
        $data['jsData']    = json_encode($info);

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
