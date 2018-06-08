<?php
/**
 * 会员身份转换（个人转企业）
 * Created by PhpStorm.
 * User: JoJoJing
 * Date: 2016/9/3
 * Time: 14:23
 */
class uType_json extends worker{
    function __construct($options){
        parent::__construct($options,[60132]);
    }

    function run(){
        $options = $this->options;
        $user           = new user();
        $mongo          = new mgdb();
        $id             = (isset($options['id']) && strlen($options['id']) == 32) ? $options['id'] : '';
        $type           = isset($options['type']) ? intval($options['type']) : 0;
        $companyName    = isset($options['companyName']) ? $options['companyName'] : '';
        $comLicenseCode = isset($options['comLicenseCode']) ? $options['comLicenseCode'] : '';
        $u_organize     = isset($options['u_organize']) ? $options['u_organize'] : '';

        if(empty($id)){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        if($type === 0){
            $this->show(message::getJsonMsgStruct('1002','不能转换为个人用户'));
            exit;
        }

        /** -- start 判断该用户是否在商城开店 --**/
        $token = apis::request('u/api/checkOpenShop.json',['uid' => $id], true);
        if($token['code'] != 1001){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $token = $token['data'];
        if($token['code'] == 1){  //已开店
            $this->show(message::getJsonMsgStruct('1002','您已在商城开店铺，不能升级为企业会员'));
            exit;
        }
        if($token['code'] == 2){  //正在开店
            $this->show(message::getJsonMsgStruct('1002','您已申请在商城开店铺，不能升级为企业会员'));
            exit;
        }
        /** -- end 判断该用户是否在商城开店 --**/



        if(empty($companyName)){
            $this->show(message::getJsonMsgStruct('1002','公司名称不能为空'));
            exit;
        }
        if(empty($comLicenseCode)){
            $this->show(message::getJsonMsgStruct('1002','营业执照编号不能为空'));
            exit;
        }
        if(empty($u_organize)){
            $this->show(message::getJsonMsgStruct('1002','组织机构类型不能为空'));
            exit;
        }

        if(!$user->uniqueUserInfo(6,$companyName)){
            $this->show(message::getJsonMsgStruct('1002','公司名称已被占用'));
            exit;
        }
        if(!$user->uniqueUserInfo(7,$comLicenseCode)){
            $this->show(message::getJsonMsgStruct('1002','营业执照已被占用或格式不对'));
            exit;
        }

        //获取当前用户信息
        $userInfo = apis::request('u/api/publicUserInfo.json', ['u_id' => $id], true);
        if($userInfo['code'] != 1001){
            $userInfo['data'] = array();
        }
        $userInfo = $userInfo['data'];

        if(empty($userInfo)){
            $this->show(message::getJsonMsgStruct('1002','无当前用户信息'));
            exit;
        }elseif($userInfo['u_type'] == $type){
            $this->show(message::getJsonMsgStruct('1002','与当前身份一致,无需修改'));
            exit;
        }else{
            $db = new MySql();
            try {
                $db->beginTRAN();
                //原个人会员数据
                $sql = "select * from t_user_person where u_id = '".$id."'";
                $oldData = $db->getRow($sql);

                //修改会员身份类型
                $upParam = array(
                    'u_type'            => $type,
                    'u_auth'            => substr($userInfo['u_auth'],0,2).'00',
                    'u_lastUpdateTime'  => F::mytime(),
                );
                $updateType = $db->update('t_user',$upParam," u_id = '".$id."'");
                if(!$updateType){
                    throw new Exception('-1');
                }

                //查询认证表mongodb是否有认证信息
                $whereAuth = array(
                    "au_uid"    => array("value" => "{$id}"),
                    "au_type"   => array("value" => 2)
                );
                $isAuthInfo = $mongo->where($whereAuth)->get('auth');
                if(!empty($isAuthInfo)){
                    //若有认证信息，把认证状态改为0【未认证】
                    $updateAuth = array(
                        'au_result' => intval(-1),
                        'au_eid'    => $_SESSION['userID'],
                        'au_etime'  => F::mytime(),
                        'au_metime' => time(),
                    );
                    $authInfo = $mongo->where($whereAuth)->set($updateAuth)->update('auth');
                    if(!$authInfo){
                        throw new Exception('-1');
                    }
                }

                //修改当前用户身份证并把u_isbox改为失效-1（t_user_person）,不删除t_user_person记录
                $perUpdate = array(
                    'u_certNum' => empty($userInfo['u_certNum']) ? '' : $userInfo['u_certNum']."-xxx",
                    'u_personUpdateTime' => F::mytime(),
                    'u_isbox'            => -1,
                );
                $perUpData = $db->update('t_user_person', $perUpdate, " u_id = '".$id."'");
                if(!$perUpData){
                    throw new Exception('-1');
                }

                //添加数据到t_user_company
                $param = array(
                    'u_id'              => $id,
                    'u_comLicenseCode'  => $comLicenseCode,
                    'u_organize'        => $u_organize,
                    'u_comUpdateTime'   => F::mytime(),
                    'u_companyName'     => $companyName,
                );
                $addData = $db->insert('t_user_company',$param);
                if(!$addData){
                    throw new Exception('-1');
                }

                //写日志
                $data['memo'] = '会员身份转换(个人转企业)';
                $data = array(
                    'memo'                     => '会员身份转换(个人转企业)',
                    '会员昵称'                 => $userInfo['u_nick'],
                    '转换前用户类型'           => ($userInfo['u_type'] == 1) ? '企业身份' : '个人身份',
                    '转换后用户类型'           => ($type == 1) ? '企业身份' : '个人身份',
                    '转换前证件类型'           => ($oldData['u_certType'] == 1) ?  '大陆身份证' : '非大陆身份证',
                    '转换前身份证'             => $oldData['u_certNum'],
                    '转换后身份证'             => $perUpdate['u_certNum'],
                    '转换前认证状态'           => (isset($authInfo[0]['au_result']) && $authInfo[0]['au_result']) ? '已认证' : ((isset($authInfo[0]['au_result']) && $authInfo[0]['au_result'] == 2) ? '认证中' : '未认证'),
                    '转换后认证状态'           => '未认证',
                    '公司名称'                 => $companyName,
                    '营业执照编号'             => $comLicenseCode,
                    '组织机构类型'             => $u_organize,
                    '修改时间'                 => F::mytime(),
                    '操作人'                   => $_SESSION['userID'],
                );

                //$data = array_merge($oldData,$param,$data['memo'],$upParam);
                log::writeLogMongo(609, 't_user_company', $id, $data);

                $db->commitTRAN();
                $this->show(message::getJsonMsgStruct('1001','成功转换身份'));
                exit;
            }catch(Exception $e){
                $db->rollBackTRAN();
                $this->show(message::getJsonMsgStruct('1002','操作失败'));
                exit;
            }
        }
    }

    private function curl_post ( $url, $data, $param = NULL ) {
        $curl = curl_init( $url );// 要访问的地址
        curl_setopt( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
        curl_setopt( $curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );// 显示输出结果
        curl_setopt( $curl, CURLOPT_POST, TRUE ); // post传输数据
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );// post传输数据
        $res = curl_exec( $curl );
        curl_close( $curl );
        return $res;
    }
}