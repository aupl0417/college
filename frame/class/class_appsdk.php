<?php

/**
 * 开放平台API封装类。
 * 支持taobao,qq,sina,renren等
 *
 * @author flybug
 * @data 2013-02-21
 *
 */

/**
 *
 * 淘宝API。
 * 采用oauth2.0的协议
 *
 */
class goodsAPI {

    //根据正则截取信息
    public function regex2info($reg, $fh) {
        preg_match($reg, $fh, $infoArr);
        if (empty($infoArr)) {
            return false;
        }
        $str = iconv('GBK', 'UTF-8', $infoArr[1]);
        return $str;
    }

    //通过商品id获得指定平台的商品基本信息
    public function getGoodsInfoByID($id, $falg = 'taobao') {
        switch ($falg) {
            default :
            case 'taobao':
                $url = 'https://item.taobao.com/item.htm?id=' . $id;
                $text = file_get_contents($url);
                $info = array();
                $info['gid'] = $id;
                //主图
                $imgReg = '/<img[^>]*id="J_ImgBooth"[^>]*src=\"([^"]*)\"[^>]*>/';
                $info['img'] = $this->regex2info($imgReg, $text);
                if (substr($info['img'], 1, 4) != 'http') {
                    $info['img'] = 'http:' . $info['img'];
                }
                //标题
                $titleReg = '/<title>([^<>]*)<\/title>/';
                $info['title'] = $this->regex2info($titleReg, $text);

                //掌柜名称（根据是否有掌柜名称，判断是淘宝还是天猫）
                $sellerNameReg = '/<a[^>]*class=\"tb-seller-name\"[^>]*>([^<>]*)<\/a>/';
                $sellerName = $this->regex2info($sellerNameReg, $text);

                if (!$sellerName) { //如果没有掌柜名称，说明是天猫的商品
                    //淘宝的店铺链接
                    $shopLinkReg = '/<a[^>]*class="slogo-shopname"[^>]*href=\"([^"]*)\"[^>]*>/';

                    //店铺名称
                    $shopNameReg = '/<a[^>]*class="slogo-shopname"[^>]*>[^>]*<strong>([^<>]*)<\/strong>/';
                    $info['istmall'] = 1;
                } else {
                    //掌柜名称
                    $info['sellerName'] = trim($sellerName);

                    //价格
                    $priceReg = '/<em[^>]*class="tb-rmb-num"[^>]*>([^>]*)<\/em>/';
                    $info['price'] = $this->regex2info($priceReg, $text);


                    //淘宝的店铺链接
                    $shopLinkReg = '/<div[^>]*class="tb-shop-info-ft"[^>]*>[^>]*<a[^>]*href=\"([^"]*)\"[^>]*>/';

                    //店铺名称
                    $shopNameReg = '/<div[^>]*class="tb-shop-name"[^a]*<strong>[^>]*<a[^>]*title=\"([^"]*)\"[^>]*>/';
                    $info['istmall'] = 0;
                }

                //店铺url
                $info['shopLink'] = $this->regex2info($shopLinkReg, $text);
                if (substr($info['shopLink'], 1, 4) != 'http') {
                    $info['shopLink'] = 'http:' . $info['shopLink'];
                }
                //店铺名称
                $info['shopName'] = $this->regex2info($shopNameReg, $text);
                return $info;
                break;
            case 'jd':
                $url = 'http://item.jd.com/' . $id . '.html';
                $text = file_get_contents($url);

                $info = array();
                $info['gid'] = $id;
                //主图
                $imgReg = '/<div[^>]*id="spec-n1"[^>]*>[^>]*<img[^>]*src="([^"]*)"[^>]*>/';
                $info['img'] = $this->regex2info($imgReg, $text);

                //标题
                $titleReg = '/<div[^>]*id="name"[^>]*>[^>]*<h1>([^>]*)<\/h1>/';
                $info['title'] = $this->regex2info($titleReg, $text);

                //店铺链接
                $shopLinkReg = '/<a[^>]*class="name"[^>]*href="([^"]*)"[^>]*>/';
                $info['shopLink'] = $this->regex2info($shopLinkReg, $text);

                //店铺名称
                $shopNameReg = '/<a[^>]*class="name"[^>]*>([^>]*)<\/a>/';
                $info['shopName'] = $this->regex2info($shopNameReg, $text);
                return $info;
                break;
        }
    }

}

class taobaoAPI {

    private $appKey;
    private $secretKey;
    private $format = 'json'; //返回格式json、xml
    private $connectTimeout;
    private $readTimeout;
    private $signMethod = "md5";
    private $apiVersion = '2.0';
    private $sdkVersion = "top-sdk-php-20140106";
    private $tokenObj = NULL;
    private $code; //授权码
    private $codeUrl = 'https://oauth.taobao.com/authorize'; //授权码获得地址
    private $token = ''; //访问令牌
    private $tokenUrl = 'https://oauth.taobao.com/token'; //访问令牌获得地址
    private $gateway;
    private $gatewayUrl = array('http://gw.api.taobao.com/router/rest', 'https://eco.taobao.com/router/rest'); //api调用网关，第一种是签名方式，第二种是免签

    /*
      private $apiMethodArray = array(
      'taobao.areas.get',//需返回的字段列表.可选值:Area 结构中的所有字段;多个字段之间用","分隔.如:id,type,name,parent_id,zip.
      'taobao.categoryrecommend.items.get',//
      'taobao.feedback.add'
      );
     */

    /*
     * 获得ip信息
     * Array
      (
      [code] => 0
      [data] => Array
      (
      [country] => 中国
      [country_id] => CN
      [area] => 华南
      [area_id] => 800000
      [region] => 广东省
      [region_id] => 440000
      [city] => 佛山市
      [city_id] => 440600
      [county] =>
      [county_id] => -1
      [isp] => 电信
      [isp_id] => 100017
      [ip] => 113.105.225.71
      )

      )
     */

    static public function getIpInfoByTaobao($ip) {
        $url = "http://ip.taobao.com/service/getIpInfo.php?ip=$ip";
        return F::objectToArray(json_decode(F::curl($url)));
    }

    public function __construct($index = 0, $getwaytype = 0, $format = 'json') {
        $this->appKey = '';
        $this->secretKey = '';
        $this->gateway = '';
        $this->format = $format;
    }

//通过访问令牌对象获得相关信息
    public function getInfoByTokenObj($key) {
        return is_null($this->tokenObj) ? '' : $this->tokenObj->$key;
    }

//通过访问令牌对象获得access_token
    public function getToken() {
        return $this->getInfoByTokenObj('access_token');
    }

//返回tokenobj
    public function getTokenObj() {
        return $this->tokenObj;
    }

//settokenobj
    public function setTokenObj($v) {
        return $this->tokenObj = $v;
    }

//根据商品的url获得淘宝商品id，兼容直接输入taobaoid。
    public static function getTaobaoIdByTaobaoUrl($url) {
        $url = htmlspecialchars_decode($url);
        if (preg_match('/^\d*$/', $url, $out)) {
            return $url;
        } else if (preg_match('/(&|\?)id=\d*/', $url, $out)) {
            return preg_replace('/(&|\?)id=/', '', $out[0]);
        } else {
            return '';
        }
    }

    /*     * ***********************************************************
     * 
     * 淘宝登录授权
     * 
     * memo：淘宝登录授权采用的是签名和免签两种方式
     * 
     * *********************************************************** */

    function getTaoApiGenerateSign($params) {
        ksort($params); //先把请求参数排序
        $stringToBeSigned = $this->secretKey;
        foreach ($params as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->secretKey;
        return strtoupper(md5($stringToBeSigned));
    }

//获得淘宝回调地址
    public function getReturnURL($url) {
        return BASEURL . "/$url";
    }

    /*     * *********************************************************************
     * 
     * 获取授权码或授权回调。
     * type可选参数有code/token
     * 
     * 备注：
     * code适合服务端调用，需要调用用code再次获得accesstoken；
     * token适合客户端js调用，可以直接返回accesstoken
     * 
     * ********************************************************************* */

    public function getTabaoLoginReturn($rurl, $type = 'code') {
        return sprintf('%s?response_type=%s&client_id=%s&redirect_uri=%s', $this->codeUrl, $type, $this->appKey, urlencode($rurl));
    }

//得到登录注销的淘宝地址
    public function getTabaoLoginOffReturn($rurl) {
        return sprintf('https://oauth.taobao.com/logoff?client_id=%s&redirect_uri=%s', $this->appKey, urlencode($rurl));
    }

//根据授权码获得访问令牌（出错返回空）
    public function getTokenObjByCode($code, $rurl) {
        $p = array(
            'client_id' => $this->appKey,
            'client_secret' => $this->secretKey,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $rurl
        );
        $ret = F::curl($this->tokenUrl, $p);
        $this->tokenObj = is_null($ret) ? null : json_decode($ret);
        return $this->tokenObj;
    }

//根据刷新令牌获得新的访问令牌（出错返回空）
    public function getNewTokenObjByRefreshingToken($RefreshingToken) {
        $p = array(
            'client_id' => $this->appKey,
            'client_secret' => $this->secretKey,
            'grant_type' => 'refresh_token',
            'refresh_token' => $RefreshingToken
        );
        $ret = F::curl($this->tokenUrl, $p);
        $this->tokenObj = is_null($ret) ? null : json_decode($ret);
        return $this->tokenObj;
    }

    /*     * ***********************************************************
     * 
     * 函数功能：淘宝API调用
     * 根据访问令牌执行淘宝API调用，分(0-签名和1-免签)两种方式，返回json或xml对象，出错返回null。
     * 
     * 参数：
     * $method:API调用函数
     * $params:API业务参数组
     * $token:access_token
     * 
     * *********************************************************** */

//签名方式
//免签方式（需要授权）
    public function execute($method, $apiparams, $mode = 0, $token = '') {
//组装系统参数
        if ($mode == 0) {
            $sysParams = array(
                'method' => $method,
                'timestamp' => date("Y-m-d H:i:s"),
                'format' => $this->format,
                'app_key' => $this->appKey,
                'v' => $this->apiVersion,
                'sign_method' => $this->signMethod,
                'partner_id' => $this->sdkVersion
            );
            if ($token != '') {
                $sysParams['session'] = $token;
            }
            $sysParams['sign'] = $this->getTaoApiGenerateSign(array_merge($apiparams, $sysParams)); //签名					
        } else {
            $sysParams = array(
                'method' => $method,
                'access_token' => $token,
                'format' => $this->format,
                'v' => $this->apiVersion,
                'timestamp' => date("Y-m-d H:i:s")
            );
        }

//发起HTTP请求
        try {
            $ret = F::curl($this->gateway, array_merge($sysParams, $apiparams));
        } catch (Exception $e) {
            log::writelog($this->gateway . ',code:', $e->getCode() . 'msg:' . $e->getMessage(), 'appsdk');
            $this->tokenObj = null;
        }
//解析TOP返回结果
        $respWellFormed = false;
        if ("json" == $this->format) {
            $respObject = json_decode($ret);
            if (null !== $respObject) {
                $respWellFormed = true;
                foreach ($respObject as $propKey => $propValue) {
                    $respObject = $propValue;
                }
            }
        } else if ("xml" == $this->format) {
            $respObject = @simplexml_load_string($ret);
            if (false !== $respObject) {
                $respWellFormed = true;
            }
        }

//返回的HTTP文本不是标准JSON或者XML，记下错误日志
        if (false === $respWellFormed) {
            log::writelog('返回格式不对，请检查', 'appsdk');
            $this->tokenObj = null;
            return false;
        }

//如果TOP返回了错误码，记录到错误日志中
        if (isset($respObject->code)) {
            log::writelog('淘宝API返回错误代码：' . $ret, 'appsdk');
            $this->tokenObj = $respObject;
            return false;
        }
        $this->tokenObj = $respObject;
    }

}

?>