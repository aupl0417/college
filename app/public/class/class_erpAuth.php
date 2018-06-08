<?php
/*=============================================================================
#     FileName: class_erpAuth.php
#         Desc: 调用erp的API
#       Author: Wuyuanhang
#        Email: QQ:2881319821
#     HomePage:
#      Version: 0.0.1
#   LastChange: 2016-10-15 14:52:16
#      History:
=============================================================================*/
class erpAuth {
    const ERP_ECRET_KEY    = 'OPO9LSE2NEDC0FR45TGDKHY67U8Z8IK9';
    const APPKEY         = 'C000000000000003';
    const ERP_OPEN_API_URL = 'https://open.dttx.com/';

    private $error;

    /**
     * 发送HTTP请求方法
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    public function request($url, $params=array(), $method = 'GET', $header = array(), $multi = false){
        if (empty($url)) {
            $this->error = '接口地址错误';
            return false;
        }

        $url  = self::ERP_OPEN_API_URL.$url;

        $params['appKey']    = self::APPKEY;
        $params['signValue'] = md5(http_build_query($params)."&".self::ERP_ECRET_KEY);
        $params['app']       = 'work';

        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );

        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
        case 'POST':
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        case 'GET':
        default:
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if($error) {
            $this->error = $error;
            return false;
        }
        return  $data;
    }

    function getError(){
        return $this->error;
    }
}
