<?php
/*=============================================================================
#     FileName: printIdentity.php
#         Desc: 打印学员证
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-11-18 10:08:12
#      History:
=============================================================================*/
class printIdentity extends worker{

    public function run(){
        include_once(FRAMEROOT."/lib/mpdf/mpdf.php");
        $options  = $this->options;

        if (!isset($options['id'],$options['clID']) || empty($options['id']) || empty($options['clID'])) {
            die;
        }

        $db = new MySql();
        $classInfo = $db->getRow("SELECT cl_name,cl_number,tangCollege FROM tang_class WHERE cl_id='{$options['clID']}'");

        $sql = "SELECT um.username,um.trueName,um.mobile,um.code,um.auth,tse.tse_team,userId FROM tang_ucenter_member um LEFT JOIN tang_student_enroll tse ON tse.tse_userId=um.id
            WHERE um.id='{$options['id']}' AND tse.tse_status=2";
        $userInfo = $db->getRow($sql);

        if (empty($userInfo)) {
            die('没有会员的报名资料');
        }

        if (empty($info['code'])) {
            $newUserInfo = apis::request('/college/api/getUser.json',['userId'=>$userInfo['userId']],true);
            if (1001 != $newUserInfo['code']) {
                die('没有会员资料');
            }
            $userInfo['code'] = $newUserInfo['data']['code'];
        }
        
        $info = array('term' => $classInfo['cl_number'],'trueName'=>$userInfo['trueName']);
        
        //aupl修改开始
        $title = str_replace('C ', 'C+', $classInfo['cl_name']);
        $name  = $userInfo['trueName'];
        $userInfo['auth'] = '1001';
        
        if(substr($userInfo['auth'], 2, 1) == '0'){
            $cityInfo = $db->getRow('select br_parentId,a_id from tang_branch left join tang_area on br_parentId=a_code where br_id="' . $classInfo['tangCollege'] . '"');
            $provinceName = $this->getProvinceId($cityInfo['a_id']);
            
            $title = str_replace(array('省', '市', '县'), array('', '', ''), $provinceName) . '专场 第' . $info['term'] . '期';
            $name  = $userInfo['username'];
        }
        
        $content = '<div style="height:125mm;width:85mm;text-align:center;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td height="113">&nbsp; </td>
                            </tr>
                            <tr><td height="30" align="center" lang="zh-CN"><strong style="font-size:16pt">第'.$info['term'].'期</strong></td> </tr>
                            <tr>
                                <td height="217" align="center"> 
                                    <div style="padding:10px;">
                                        <img width="190" alt="" src="https://workcollege.dttx.com/public/code?u='.urlencode("https://u.dttx.com/register/?code={$userInfo['code']}") .'">
                                    </div>
                                </td> 
                           </tr>
                           <tr>
                              <td height="20" align="center" lang="zh-CN">
                                  <strong style="font-size:16pt">'.$this->convertHtml($name).'&nbsp; &nbsp; 第'.$userInfo['tse_team'].'组</strong>
                              </td>
                           </tr>
                       </table>
                   </div> ';

        $html = ' <div lang="zh-CN"> '. $content .' </div> ';
        $mpdf = new mPDF('-aCJK', [85, 125], '', '', 0, 0, 0, 0);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetTitle("{$name}学员证({$title})");
        //aupl修改结束
        
        $mpdf->autoLangToFont = true;
        $mpdf->WriteHTML($html);
        //$mpdf->Output("{$userInfo['username']}学员证.pdf",'D');
        $mpdf->Output();
        exit;
    }

    private function convertHtml($str){
        $str = trim(preg_replace('/\\\u([0-9a-f]{4})/i', '&#x${1};', json_encode($str)), '"');
        return $str;
    }
    
    //获取省份id
    private function getProvinceId($cityId){
        $sql  = 'select a_code,a_fkey,a_name from tang_area where a_id="' . $cityId . '"';
        $db   = new MySql();
        $data = $db->getRow($sql);
    
        if($data['a_fkey'] != 0){
            return self::getProvinceId($data['a_fkey']);
        }else {
            return $data['a_name'];
        }
    }
}
