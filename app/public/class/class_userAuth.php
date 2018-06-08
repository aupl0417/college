<?php
/*=============================================================================
#     FileName: class_auth.php
#         Desc: 个人，企业验证类 
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage: 
#      Version: 0.0.1
#   LastChange: 2016-05-18 11:31:41
#      History:
=============================================================================*/
class userAuth {
    protected $db;
    protected $mgdb;

    function __construct($db=NULL, $mgdb=NULL){
        $this->db   = is_null($db) ? new MySql() : $db;
        $this->mgdb = is_null($mgdb) ? new mgdb() : $mgdb;
    }

    /* --------------------------------------------------------------------------*/
    /**
     * @Synopsis  根据验证类型获取认证状态
     *
     * @Param $userID    用户ID      必需    string(32)  
     * @Param $userClass 用户类型    必需    int
     * @Param $type      认证类型    必需    int
     *
     * @Returns   int -1认证失败 0 未认证    1 成功  2 认证中
     */
    /* ----------------------------------------------------------------------------*/
    //
    public function getAuthStateByType($userID, $userClass, $type){
        $where = array(
            'au_uid'   => array('value' => "$userID"),
            'au_type'  => array('value' => $type,'num'      => 1),
            //'au_utype' => array('value' => $userClass,'num' => 1),
        );

        if (!in_array($type,array(0,1))) {
            $where['au_utype'] = array('value' => $userClass);
        }

        /*获取用户认证信息*/
        $userAuthInfo = $this->userAuthInfo($userID);
        $userAuthInfo = str_split($userAuthInfo['u_auth']);
        $authed       = $userAuthInfo[$type];

        $order = array('au_mctime'=>-1);
        $data = $this->mgdb->orderBy($order)->where($where)->limit(1)->get('auth');

        if ($data) {
            $data = current($data);
            if (in_array($data['au_result'],array(-1,2))) {
                $authed = $data['au_result'];
            }
        }
        return intval($authed);
    }

    /* --------------------------------------------------------------------------*/
    /**
     * @Synopsis  根据认证类型获取认证的表单信息
     *
     * @Param $userID        用户ID      必需    string(32)  
     * @Param $type          认证类型    必需    int
     * @Param $userClass     用户类型    必须    int
     *
     * @Returns   array
     */
    /* ----------------------------------------------------------------------------*/
    public function getAuthInfoByType($userID, $type, $userClass){
        $where = array(
            'au_uid'   => array('value' => "$userID"),
            'au_utype' => array('value' => $userClass,'num' => 1),
            'au_type'  => array('value' => $type,'num'      => 1),
        );

        $order = array('au_mctime'=>-1);
        $data = $this->mgdb->orderBy($order)->where($where)->limit(1)->get('auth');
        if ($data) {
            $data = current($data);
        }
        return $data;
    }

    /* --------------------------------------------------------------------------*/
    /**
     * @Synopsis  认证的类型字段
     * @Param $userClass 用户类型    必需    int   
     * @Returns   array
     */
    /* ----------------------------------------------------------------------------*/
    private function getAuthList($userClass){
        if ($userClass == 0) {
            $authList = array('mobile','email','person','personSeller');
        }else{
            $authList = array('mobile','email','company','union');
        }

        return $authList;
    }

    //获取用户所有的认证信息
    public function getAuthInfo($userID, $userClass){
        /*获取用户认证信息*/
        $userAuthInfo = $this->userAuthInfo($userID);
        $userAuthInfo = str_split($userAuthInfo['u_auth']);
        /*获取用户信息键值*/
        $authList = $this->getAuthList($userClass);
        /*用户认证信息分解*/
        $where = array(
            'au_utype' => array('value'=>$userClass,'num'=>1),
            'au_uid'   => array('value' => $userID),
        );

        $authInfo = array();
        foreach ($userAuthInfo as $k=>$v) {
            foreach($authList as $key=>$auValue){
                if($k == $key){
                    $authInfo[$k]['authed'] = $v;
                }
            }
        }

        //au_result -1 验证失败， 0 未认证 1 认证通过 2 认证中 
        foreach ($authList as $k=>$v) {
            $where['au_type'] = array(
                'value' => $k,
                'num'   => 1,
            );
            $data = $this->mgdb->where($where)->limit(1)->orderBy(array('au_mctime'=>-1))->get('auth');
            $authInfo[$k]['detail'] = array();
            if (!empty($data)) {
                $data                    = current($data);

                if (in_array($data['au_result'],array(-1,2))) {
                    $authInfo[$k]['authed']  = $data['au_result'];
                }
                $authInfo[$k]['au_memo'] = $data['au_memo'];
                $authInfo[$k]['detail']  = $data;
                //switch($data['au_result']){
                //case 0:
                //    $authInfo[$k]['authed'] = 2;
                //    break;
                //}
            }
        }

        $reAuthInfo = array();
        unset($v);
        foreach ($authInfo as $k=>$v) {
            foreach($authList as $key=>$au){
                if($k == $key){
                    $reAuthInfo[$au] = $v;
                }
            }
        }

        return $reAuthInfo;
    }

    //认证中的认证记录
    private function userAuthing($userID){
        $where = array(
            'au_uid'    => $userID,
            'au_result' => array('value' => 2,'num' => 1),
        );

        $data = $this->mgdb->orderBy(array('au_mctime'=>-1))->where($where)->get('auth')->whereNe('au_uid',null);
        if ($data) {
            $data = array_column($data,'au_result','au_type');
        }
        return $data;
    }

    //用户u_auth
    private function userAuthInfo($userID){
        $user     = new user();
        $userInfo = $user->getUserByID($userID, 'u_auth');
        return $userInfo;
    }

    //认证列表
    public function authLog($options){
        $order = array('au_mctime'=>-1);

        $length = !isset($options['length']) ? 10 : $options['length'];
        $start  = !isset($options['start']) ? 0 : $options['start'];
        $where  = array(
            'au_type'  => array('value' => $options['au_type'],'num' => 1),
            'au_utype' => array('value' => $options['au_utype'],'num' => 1)
        );

        if(isset($options['search'])){
            foreach($options['search'] as $k => $v){
                switch($k){
                case 'au_ctime'://创建时间
                    if(array_key_exists('1', $v)){//如果传来了两个参数
                        $minDate = strtotime($v[0]['value']);
                        $maxDate = strtotime($v[1]['value']);	
                    }else{//如果只传来了一个参数							
                        if($v['filter'] == 'gte'){//最小值
                            $minDate = strtotime($v['value']);
                            $maxDate = time();//$minDate + 30 * 86400;
                        }else{//最大值
                            $maxDate = strtotime($v['value']);
                            $minDate = 0;//$maxDate - 30 * 86400;								
                        }
                    }
                    $between = array(
                        'key'     => 'au_mctime',
                        'minDate' => $minDate,
                        'maxDate' => $maxDate,
                    );
                    break;
                default:
                    $where[$k] = [
                        'value' => $v['value'],
                        'num' => $v['num'],
                        ];
                    break;
                }
            }
        }

        $this->mgdb = $this->mgdb->where($where)->whereNe('au_uid',null);

        if (isset($between)) {
           $this->mgdb = $this->mgdb->whereBetweenNe($between['key'],$between['minDate'],$between['maxDate']);
        }

        $mgdb            = clone $this->mgdb;
        $recordsFiltered = intval($this->mgdb->count('auth'));

        $list= $mgdb->limit($length)->offset($start)->orderBy($order)->get('auth');

        //print_r($list);exit;
        $list['recordsFiltered'] = $recordsFiltered;
        return $list;
    }

    //根据$id获取认证信息
    public function getAuthInfoById($auId){
        $where = array(
            '_id' => new MongoId($auId)
        );

        $data = $this->mgdb->where($where)->limit(1)->get('auth');
        if ($data) {
            $data = current($data);
        }
        return $data;
    }
}
?>
