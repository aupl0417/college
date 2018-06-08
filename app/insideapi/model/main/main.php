<?php

/**
 * desc
 * Created by ranqin
 * Time: 2016/5/27 11:38
 */
class main
{
    //数据库类实例
    //protected $db = NULL;
    //缓存实例
    // protected $cache = NULL;
    //api id
    protected $apiID = NULL;
    //partner id
    protected $partnerID = NULL;
    //app key
    protected $appKey = NULL;
    //secret key
    protected $secretKey = NULL;

    //为了保证数据统一，在Action里面取数据只能从$this->post里面取，$this->options只是作为备用，
    //protected $options = NULL;

    //用户提交的post数据
    protected $post = NULL;

    //安全类
    protected $security = NULL;

    //权限类
    //protected $privilege = NULL;

    public function run($options)
    {
        //只能通过POST方式请求
        if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) return $this->response("INVALIDE_REQUEST_METHOD:{$_SERVER['REQUEST_METHOD']}");

        //实例化一个数据库类
        $db = new MySql();
        //实例化一个缓存类
        $cache = new cache('memcache', 'api');
        //从$options里面提取$_POST数据
        $post = &apiFormat::getPostParams($options);
        //请求的接口
        $apiName = "{$options['PATH_MODEL']}.{$options['PATH_ACTION']}";
        //下面再也不想看见$options了

        /* 安全检查类 */
        $security = new apiSecurityModel($db, $cache);

        //检查SQL注入
        if ( TRUE !== $result = $security->checkInjection($post) ) {
            return $this->response($result);
        }

        /* 权限检查类 */
        $privilege = new apiPrivilegeModel($db, $cache);

        //检查接口是否存在
        $result = $privilege->isApiExists($apiName);
        if ( is_numeric($result) ) {
            //接口存在，返回的是接口id
            $this->apiID = $result;
        } else {
            //接口不存在，返回错误代码
            return $this->response($result);
        }

        //检查参数是否完整合法
        if ( TRUE !== $result = $security->isParamFull($this->apiID, $post) ) {
            return $this->response($result);
        }

        //检查加密摘要是否正确
        if ( TRUE !== $result = $security->checkSign($post, $this->secretKey) ) {
            return $this->response($result);
        }

        //检查合作伙伴
        $post['partner_id'] = isset($post['partner_id']) ? $post['partner_id'] : NULL;
        $result = $privilege->checkDeveloper($post['partner_id']);
        if ( strpos($result, '_') ) {
            //有下划线（验证失败），返回的是错误代码
            return $this->response($result);
        } else {
            //没有下划线（验证成功），返回的是partner id
            $this->partnerID = $result;
        }

        //检查app
        $post['app_key'] = isset($post['app_key']) ? $post['app_key'] : NULL;
        $result = $privilege->checkApp($post['partner_id'], $post['app_key']);
        if ( is_string($result) ) {
            //验证失败，返回的是错误代码
            return $this->response($result);
        } else {
            //验证成功，返回的是app
            list($this->appKey, $this->secretKey) = $result;
        }

        //检查应用是否具有调用接口的权限
        if ( TRUE !== $result = $privilege->hasPrivilege($this->appKey, $this->apiID) ) {
            return $this->response("$result:api=$apiName");
        }

        //检查是否已获得授权
        $result = $privilege->needAuth($this->apiID);
        if ( $result ) {
            $auth = $privilege->checkAppAuth($this->apiID, $post['user_id'], $post['access_token']);
            if ( $auth !== TRUE ) return $this->response($auth);
        }

        //给app的接口调用次数+1
        $privilege->updateVisit($this->apiID, $this->appKey);

        //检查请求是否超时（600秒-->10分钟）
        if ( TRUE !== $result = $security->isTimeout($post, 600) ) {
            return $this->response($result);
        }

        //取消数据库查询缓存
        //$db->cache(FALSE);
        //赋值给全局属性
        //$this->db = &$db;
        //$this->cache = &$cache;
        //$this->options = $options;
        $this->post = &$post;

        $this->security = &$security;
        //$this->privilege = &$privilege;

        //检查完成，调用具体的接口

        //...
    }

    /**
     * 数据响应结果
     * @param string $code 响应代码
     * @param array $data 响应数据
     * @return NULL
     */
    protected function response($code, array &$data = NULL)
    {
        //header头的响应类型
        $contentType = NULL;
        //响应结果字符串
        $result = NULL;
        //默认响应类型为json
        $format = isset($this->post['format']) ? $this->post['format'] : 'json';
        //替换返回的数据库字段
        if ( $data ) $data = &apiFormat::replaceDataFields($data, $this->security->getApiResponseFields($this->apiID));
        //如果操作成功，但是数据为空，则返回代码
        if ( $code == 'SUCCESS' && empty($data) ) $code = 'EMPTY_RESULT';
        //如果操作成功，给返回数据加签名（返回数据好像不加签名？）
        //if( $data )$data['sign'] = apiFormat::getDataSignature($data, $this->secretKey);

        switch ($format) {
            case 'xml' :
                $contentType = 'text/xml';
                $result = apiFormat::getXmlMsgStruct($code, $data);
            break;
            case 'json' :

            default :
                $contentType = 'application/json';
                $result = apiFormat::getJsonMsgStruct($code, $data);
            break;
        }
        header("Content-Type: $contentType; charset=utf-8");
        echo $result;
        return ob_flush();
    }
}