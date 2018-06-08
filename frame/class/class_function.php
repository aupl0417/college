<?php

/**
 * @author flybug
 * @version 2.5.0
 *
 * 系统 函数
 */
//函数类库
class F {

    //类自动载入函数
	
	public static function myAutoload($class_name) {
        $cache = new cache();
        $path = $cache->get(PROJECTNAME . 'MyAutoload' . $class_name);
        if (!$path) {
            //过滤非安全字符
            $name = preg_replace('/[^0-9a-zA-Z_-]/', '', $class_name);
			if (array_key_exists($name, $GLOBALS['appclass'])) {				
				if(strpos($GLOBALS['appclass'][$name], '/') !== false){				
					$classPath = explode('/', $GLOBALS['appclass'][$name]);
					$len = count($classPath);
					$fileName = $classPath[$len - 1];
					$classPath[$len - 1] = '';
					$path = implode('/', $classPath);
					$path = WEBROOT . '/app/'. $path . "class_{$fileName}.php"; //应用类
				}else{				
					$path = APPROOT . "/class/class_{$GLOBALS['appclass'][$name]}.php"; //应用类
				}
            }
            elseif (array_key_exists($name, $GLOBALS['frameclass'])) {
                $path = FRAMEROOT . "/class/class_{$GLOBALS['frameclass'][$name]}.php"; //框架类
            } elseif (in_array($name, $GLOBALS['frameclass'])) {
                $path = FRAMEROOT . "/class/class_$name.php";
            } else {
                $path = APPROOT . "/class/class_$name.php"; //应用类
            }
            //$cache->set(PROJECTNAME . 'MyAutoload' . $class_name, $path, 0);
        }
        require($path);
    }	

    static public function showError() {
       
    }

    public static function clearRegistClassPath() {
        $cache = new cache();
        return $cache->flush();
    }

    //正则校验
    public static function regularCheck($type,$val) {
        switch ($type) {
			case 1://用户名
			case 'username':
				if (!preg_match('/^[a-zA-Z\x{4e00}-\x{9fa5}]{1}[\x{2027}·a-zA-Z0-9\_\x{4e00}-\x{9fa5}]{4,18}[a-zA-Z0-9\x{4e00}-\x{9fa5}]{1}$/u', $val) || preg_match('/^(大唐|dt|客服|管理员|系统管理员)/i', $val) || preg_match('/(全返|赠送|大唐天下|dttx|大唐|dt|大堂|云联惠|云联|yunlianhui|yunlian|唐人街|云连惠|云连会|云支付|云加速|云数据|芸联惠|芸连惠|芸连会|芸联会|云联汇|云连汇|芸联汇|芸连汇|匀连惠|匀联惠|匀联汇|云联惠|老战士|云转回|匀加速|零购|老战士|云回转|匀加速|零购|云支付|成谋商城|脉单|众智云|麦点|秀吧|一点公益|商城联盟|客服|like)/i', $val)) {
				return false;
			}else{
				return true;
			}
			case 2://手机
			case 'mobile':
			if (!preg_match('/^1[3|4|5|7|8]\d{9}$/', $val)) {
				return false;
				}else{
				return true;
			}
			case 3://邮箱
			case 'email':
			if (!preg_match('/^([0-9A-Za-z\-_\.]+)@([0-9a-z]+\.[a-z]{2,3}(\.[a-z]{2})?)$/i', $val)) {
				return false;
				}else{
				return true;
			}
			case 4://组织机构代码证编号
			case 'orgCode':
			if (!preg_match('/^[0-9A-Z]{8}\-[0-9A-Z]{1}$/', $val)) {
				return false;
				}else{
				return true;
			}
			case 5://税务登记证编号
			case 'taxCode':
			if (!preg_match('/^[\w\-]{10,}$/', $val)) {
				return false;
				}else{
				return true;
			}
			case 7://营业执照
			case 'license':
			if (!preg_match('/^[a-z\d]{1,20}$/i', $val)) {
				return false;
				}else{
				return true;
			}
			case 8://雇员登录
			case 'employee':
			if (!preg_match('/^dttx\d{5}$/', $val)) {
				return false;
				}else{
				return true;
			}
			case 9://用户敏感词
            case 'sensitivewords':
			if (preg_match('/^(tel|pos|wifi)1[3|4|5|7|8]\d{9}$/', $val) || preg_match('/客服|管理员|系统管理员/', $val)) {
				return false;
				}else{
				return true;
			}
			case 10://验证用户是否是临时用户
            case 'temporary':
			if (preg_match('/^(tel|pos|wifi)1[3|4|5|7|8]\d{9}$/', $val)) {
				return true;
                }else{
				return false;
			}
            case 11://公司名称
            case 'companyName':
			if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $val) || preg_match('/(全返|赠送|大唐天下|dttx|大唐|dt|大堂|云联惠|云联|yunlianhui|yunlian|唐人街|云连惠|云连会|云支付|云加速|云数据|芸联惠|芸连惠|芸连会|芸联会|云联汇|云连汇|芸联汇|芸连汇|匀连惠|匀联惠|匀联汇|云联惠|老战士|云转回|匀加速|零购|老战士|云回转|匀加速|零购|云支付|成谋商城|脉单|众智云|麦点|秀吧|一点公益|商城联盟|客服|like)/i', $val)) {
				return false;
            }else{
				return true;
			}
			case 12://登陆输入用户名
			case 'login':			
			if (preg_match('/\s|\'|\"|\#|\-{2}|\+|\;|\*|\//u', $val)) {
				return false;
            }else{
				return true;
			}
			
			default:
			return false;
		}
    }	
	

    /*     * ******************************************************************************************************
     * 
     * 
     * 校验数据的函数 
     * 
     * 
     * ********************************************************************************************************* */

    //扩展md5加密函数增加安全性
    public static function MD5_EX($value) {
        return md5($value . PASSKEYWORD);
    }

    //校验是否非空
    public static function isEmpty($value) {
        return (!isset($value) || ($value === null) || (trim($value) === ""));
    }

    //校验非空
    public static function isNotNull($value) {
        return !self::isEmpty($value);
    }

    //检验长度
    public static function isMaxLength($value, $maxLength) {
        return !self::isEmpty($value) && (mb_strlen($value, "UTF-8") <= $maxLength);
    }

    //检验最大列举长度
    public static function isMaxListSize($value, $maxSize) {
        if (self::isEmpty($value)) {
            return false;
        } else {
            $list = preg_split("/,/", $value); //根据分隔符转换成数组
            return count($list) <= $maxSize;
        }
    }

    //检验最大值
    public static function isMaxValue($value, $maxValue) {
        if (self::isEmpty($value) || !is_numeric($value)) {
            return false;
        } else {
            return $value <= $maxValue;
        }
    }

    //检验最小值
    public static function isMinValue($value, $minValue) {
        if (self::isEmpty($value) || !is_numeric($value)) {
            return false;
        } else {
            return $value >= $minValue;
        }
    }

    //校验数字范围
    public static function isRange($value, $minValue, $maxValue) {
        if (self::isEmpty($value) || !is_numeric($value)) {
            return false;
        } else {
            return ($value >= $minValue) && ($value <= $maxValue);
        }
    }

    public static function isPhone($value) {
        return (preg_match('/13[0-9]\d{8}|14[0-9]\d{8}|15[0-9]\d{8}|17[0-9]\d{8}|18[0-9]\d{8}/', $value) && is_numeric($value));
    }

    /*     * ********************************************************************************************************
     * 
     * 
     * 基本功能的函数
     * 
     * 
     * ********************************************************************************************************** */

    //得到当前时间
    public static function mytime($mode = 'Y-m-d H:i:s') {
        return date($mode, time());
    }

    // 日期减1天
    public static function SubDay($ntime, $ctime) {
        $dayst = 86400;
        $oktime = $ntime - $ctime * $dayst;
        return $oktime;
    }

    //日期加1天
    public static function AddDay($ntime, $aday) {
        $dayst = 86400;
        $oktime = $ntime + $aday * $dayst;
        return $oktime;
    }

    //得到毫\微秒级时间
    public static function getMicrotime() {
        list($usec, $sec) = explode(" ", microtime());
        return sprintf('%s%03d', date('YmdHis',$sec),$usec * 1000);
    }

    //得到毫秒级时间戳(12位)
    public static function getMtID() {
        return sprintf('%012o', self::getMicrotime());
    }

    //得到唯一id
    public static function getGID($len = 32) {
        return substr(md5(self::getMtID() . rand(0, 1000)), 0, $len);
    }

    //得到流水25位时间戳
    public static function getTimeMarkID() {
        list($usec, $sec) = explode(" ", microtime());
        return sprintf('%s%06d%03d', date('YmdHis',$sec),$usec * 1000000,rand(10000, 99999));
    }

    //得到MD5
    public static function getMD5($str, $len = 32) {
        return substr(md5($str), 0, $len);
    }

    //得到高强度不可逆的加密字串
    public static function getSuperMD5($str) {
        return MD5(SHA1($str) . '@$^^&!##$$%%$%$$^&&asdtans2g234234HJU');
    }

    //得到指定分隔符分割的子串数量（例如: '1|2|3|4|5'，分隔符为'|'，子串数为竖线的出现次数+1）
    public static function getSubStrCountByDim($str, $dim = '|') {
        return substr_count($str, $dim) + 1;
    }

    //屏蔽电话号码中间的四位数字
    public static function hidtel($phone) {
        $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); //固定电话
        if ($IsWhat == 1) {
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1****$2', $phone);
        } else {
            return preg_replace('/(1[34578]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
        }
    }

    //屏蔽身份证号码中的四位生日数字
    public static function hidIDCnum($idcnum) {
        switch (strlen($idcnum)) {
            case 15:
                $cardnum = substr_replace($idcnum, "****", 8, 4);
                break;
            case 18:
                $cardnum = substr_replace($idcnum, "****", 10, 4);
                break;
            default:
                $cardnum = $idcnum;
        }
        return $cardnum;
    }

    //屏蔽邮箱号码中部分字符
    public static function hidEmail($email) {
        $arr = explode('@', $email);
        $num = substr_replace($arr[0], '***', 1, 3);
        //strlen($num)
        $mail = $num . '@' . $arr[1];
        return $mail;
    }

    public static function GetCurUrl() {
       $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
       $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
       $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
       $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
       return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }

    public static function GetIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $cip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $cip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (!empty($_SERVER['REMOTE_ADDR'])) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } else {
            $cip = '-';
        }
        return $cip;
    }

    public static function getIPTable() {
        require WEBROOT . '/frame/lib/routeros/routeros_api.class.php';
        $list = json_decode(SYS_ROUTEROS,true);
        $API = new routeros_api();
        $API->debug = true;
        $ip = [];
        foreach ($list as $v) {
            if ($API->connect($v['SERVER'], $v['MODE'], $v['PWD'])) {
                $API->write('/ip/address/print');
                $READ = $API->read(false);
                $response = $API->parse_response($READ);
                $API->disconnect();
                foreach ($response as $v) {
                    $ip[] = strstr($v['address'], '/', TRUE);
                }
            }
        }
        return $ip;
    }

    public static function testStringSafe($str) {
        return $str == addslashes($str);
    }

    //根据分隔符转换数组为字符串
    public static function trunArrayToStrByDim($arr, $dim = ',') {
        return implode($dim, $arr);
    }

    //根据分隔符转换字符串为数组
    public static function trunStrToArrayByDim($str, $dim = ',') {
        return explode($dim, $str);
    }

    //根据a=xxx&b=xxx&c=xxx字符串生成数组array('a'='xxx','b'='xxx','c'='xxx')
    public static function trunParaStrToArray($str) {
        $t = self::trunStrToArrayByDim($str, '&');
        foreach ($t as $v) {
            $a = self::trunStrToArrayByDim($v, '=');
            $r[$a[0]] = $a[1];
        }
        return $r;
    }

    //功能:判断日期格式是否正确
    public static function isdate($str, $format = "Y-m-d") {
        $strArr = explode("-", $str);
        if (empty($strArr)) {
            return false;
        }
        foreach ($strArr as $val) {
            if (strlen($val) < 2) {
                $val = "0" . $val;
            }
            $newArr[] = $val;
        }
        $str = implode("-", $newArr);
        $unixTime = strtotime($str);
        $checkDate = date($format, $unixTime);
        if ($checkDate == $str)
            return true;
        else
            return false;
    }

    public static function isdate2($str, $format = "Y-m-d H:i:s") {
        $strArr = explode(" ", $str);
        if (empty($strArr)) {
            return false;
        }

        if (!self::isdate($strArr[0]))
            return false;
        $strArr2 = explode(":", $strArr[1]);
        if (empty($strArr2)) {
            return false;
        }
        if (count($strArr2) == 3)
            return true;
        else
            false;
    }
	
	public static function verifyDateTime($date, $format = "Y-m-d H:i:s")
	{
		return DateTime::createFromFormat($format, $date);
	}

    public static function getYmdAndHis($str) {
        return explode(" ", $str);
    }

    //excel日期转换函数
    public static function excelTime($date, $time = false) {
        if (function_exists('GregorianToJD')) {
            if (is_numeric($date)) {
                $jd = GregorianToJD(1, 1, 1970);
                $gregorian = JDToGregorian($jd + intval($date) - 25569);
                $date = explode('/', $gregorian);
                $date_str = str_pad($date [2], 4, '0', STR_PAD_LEFT)
                        . "-" . str_pad($date [0], 2, '0', STR_PAD_LEFT)
                        . "-" . str_pad($date [1], 2, '0', STR_PAD_LEFT)
                        . ($time ? " 00:00:00" : '');
                return $date_str;
            }
        } else {
            $date = $date > 25568 ? $date + 1 : 25569;
            /* There was a bug if Converting date before 1-1-1970 (tstamp 0) */
            $ofs = (70 * 365 + 17 + 2) * 86400;
            $date = date("Y-m-d", ($date * 86400) - $ofs) . ($time ? " 00:00:00" : '');
        }
        return $date;
    }

    //计算两日期间隔几天
    public static function daydiff($begin_time, $end_time) {
        $begin_time = strtotime($begin_time);
        $end_time = strtotime($end_time);
        if ($begin_time < $end_time) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime - $starttime;
        $days = intval($timediff / 86400);
        return $days;
    }

    public static function checkDataType($val, $t = 'int') {
        switch ($t) {
            case 'int':
                $reg = '[0-9]*';
                break;
        }
        return ereg_replace($reg, '', $val) == '';
    }

    //删除文件
    public static function DeleteFile($fileName) {
        if (!file_exists($fileName)) {
            return true;
        } else {
            return @unlink($fileName);
        }
    }

    public static function utf8_substr($sourcestr, $cutlength, $mask = '...') {
        $returnstr = "";
        $i = 0;
        $n = 0;
        $str_length = strlen($sourcestr);
        while ($n < $cutlength && $i <= $str_length) {
            $temp_str = substr($sourcestr, $i, 1);
            $ascnum = ord($temp_str);
            if (224 <= $ascnum) {
                $returnstr .= substr($sourcestr, $i, 3);
                $i += 3;
                ++$n;
            } else if (192 <= $ascnum) {
                $returnstr .= substr($sourcestr, $i, 2);
                $i += 2;
                ++$n;
            } else if (65 <= $ascnum && $ascnum <= 90) {
                $returnstr .= substr($sourcestr, $i, 1);
                $i += 1;
                ++$n;
            } else {
                $returnstr .= substr($sourcestr, $i, 1);
                $i += 1;
                $n += 0.5;
            }
        }
        if ($cutlength < mb_strlen($sourcestr, "utf8")) {
            $returnstr .= $mask;
        }
        return $returnstr;
    }

    //校验字符串中是否存在非法字符
    public static function filter($str) {
        //拆分非法字符表为数组
        $arr = explode('|', SYS_REPLACESTRING);
        foreach ($arr as $key => $val) {
            $str = str_replace($val, '*', $str);
        }
        return $str;
    }

    //过滤危险脚本
    public static function filterScript($str) {
        $s = preg_replace('/<(script|link|style|iframe)(.|\n)*<\/\1>\s*/', '', $str);
        //$s = preg_replace( "/\s*on[a-z]+\s*=\s*(\"[^\"]+\"|'[^']+'|[^\s]+)\s*(?=>)/", '', $s );
        $s = preg_replace("/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/", '', $s);

        $s = preg_replace("/\s*(href|src)\s*=\s*(\"\s*(javascript|vbscript):[^\"]+\"|'\s*(javascript|vbscript):[^']+'|(javascript|vbscript):[^\s]+)\s*(?=>)/", '', $s);
        $s = preg_replace("/epression\((.|\n)*\);?/", '', $s);
        return $s;
    }

    //隐藏文件路径输出文件
    public static function echoFileWithoutPath($file, $filetype = 'image') {
        switch ($filetype) {
            case 'image':
                header("content-type:image/jpeg\r\n");
                break;
        }
        $fp = fopen("$file", "r");
        echo fread($fp, filesize("$file"));
        fclose($fp);
    }

    /*     * **********************************************************************************************************
     * 
     * 
     * 扩展功能的函数
     * 
     * 
     * ********************************************************************************************************** */

    //服务端执行post请求调用（正确返回reponse对象，错误返回null）
    public static function curl($url, $postFields = null) {
		$cache = new cache();
		$cacheKey = md5('curl'.json_encode(func_get_args()));
		$execTime = $cache->get($cacheKey);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);//连接等待时间 s
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);//执行超时时间 s
        //https 请求
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if (is_array($postFields) && 0 < count($postFields)) {
            $postBodyString = '';
            $postMultipart = false;
            foreach ($postFields as $k => $v) {
                if ('@' != substr($v, 0, 1)) {//判断是不是文件上传
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                } else {
                    //文件上传用multipart/form-data，否则用www-form-urlencoded
                    $postMultipart = true;
                }
            }
            $postFields = trim($postBodyString, '&');
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            }
        }
        try {
			
            $reponse = curl_exec($ch);
            if (curl_errno($ch)) {
				//log::writeLog(F::getServerIp().' - '.curl_error($ch).'   ', 'fff');
                throw new Exception(curl_error($ch), 0);
            } else {
                $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (200 !== $httpStatusCode) {
                    throw new Exception('httpStatusError', $httpStatusCode);
                }
            }
        } catch (Exception $e) {
            $reponse = null;
        }
        curl_close($ch);
        return $reponse;
    }

    public static function SpGetPinyin($str, $ishead = 0, $isclose = 1) {
        global $pinyins;
        $restr = "";
        $str = trim($str);
        $slen = strlen($str);
        if ($slen < 2) {
            return $str;
        }
        if (count($pinyins) == 0) {
            $fp = fopen(dirname(__FILE__) . "/data/pinyin.db", "r");
            while (!feof($fp)) {
                $line = trim(fgets($fp));
                $pinyins [$line [0] . $line [1]] = substr($line, 3, strlen($line) - 3);
            }
            fclose($fp);
        }
        $i = 0;
        for (; $i < $slen; ++$i) {
            if (128 < ord($str [$i])) {
                $c = $str [$i] . $str [$i + 1];
                ++$i;
                if (isset($pinyins [$c])) {
                    if ($ishead == 0) {
                        $restr .= $pinyins [$c];
                    } else {
                        $restr .= $pinyins [$c] [0];
                    }
                } else {
                    $restr .= "_";
                }
            } else if (eregi("[a-z0-9]", $str [$i])) {
                $restr .= $str [$i];
            } else {
                $restr .= "_";
            }
        }
        if ($isclose == 0) {
            unset($pinyins);
        }
        return $restr;
    }

    //将对象转换为多层数组
    public static function objectToArray($e) {
        $e = (array) $e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource')
                return;
            if (gettype($v) == 'object' || gettype($v) == 'array')
                $e[$k] = (array) self::objectToArray($v);
        }
        return $e;
    }
	


    //将XML转换为多维数组
    public static function xmlToArray($xml){
        return self::objectToArray(simplexml_load_string($xml));
    }	

    //判断是不是邮箱格式
    public static function isEmail($n) {
        return preg_match("/^[\\w\\-\\.]+@[\\w\\-\\.]+(\\.\\w+)+$/", $n);
    }

    //自动保存文件函数
    public static function filePush($path, $content) {
        if (!file_exists($path)) {
            file_put_contents($path, '');
        } else {
            $oldContent = file_get_contents($path);
        }
        $newContent = $oldContent . "\r\n" . $content;
        file_put_contents($path, $newContent);
    }

    //将数组转换成对象
    public static function arrayToObject($e) {
        if (gettype($e) != 'array')
            return;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object')
                $e[$k] = (object) self::arrayToObject($v);
        }
        return (object) $e;
    }

    public static function ExecTime() {
        $time = explode(" ", microtime());
        $usec = (double) $time[0];
        $sec = (double) $time[1];
        return $sec + $usec;
    }

    public static function GetMkTime($dtime) {
        if (!preg_match("/[^0-9]/", $dtime)) {
            return $dtime;
        }
        $dt = array(1970, 1, 1, 0, 0, 0);
        $dtime = ereg_replace("[\r\n\t]|日|秒", " ", $dtime);
        $dtime = str_replace("年", "-", $dtime);
        $dtime = str_replace("月", "-", $dtime);
        $dtime = str_replace("时", ":", $dtime);
        $dtime = str_replace("分", ":", $dtime);
        $dtime = trim(ereg_replace("[ ]{1,}", " ", $dtime));
        $ds = explode(" ", $dtime);
        $ymd = explode("-", $ds[0]);
        if (isset($ymd[0])) {
            $dt[0] = $ymd[0];
        }
        if (isset($ymd[1])) {
            $dt[1] = $ymd[1];
        }
        if (isset($ymd[2])) {
            $dt[2] = $ymd[2];
        }
        if (strlen($dt[0]) == 2) {
            $dt[0] = "20" . $dt[0];
        }
        if (isset($ds[1])) {
            $hms = explode(":", $ds[1]);
            if (isset($hms[0])) {
                $dt[3] = $hms[0];
            }
            if (isset($hms[1])) {
                $dt[4] = $hms[1];
            }
            if (isset($hms[2])) {
                $dt[5] = $hms[2];
            }
        }
        foreach ($dt as $k => $v) {
            $v = ereg_replace("^0{1,}", "", trim($v));
            if ($v == "") {
                $dt[$k] = 0;
            }
        }
        $mt = @mktime($dt[3], $dt[4], $dt[5], $dt[1], $dt[2], $dt[0]);
        if (0 < $mt) {
            return $mt;
        } else {
            return self::mytime();
        }
    }

    public static function GetDateTimeMk($mktime) {
        #compatable with PHP 5.3 & 5.4
        if ($mktime == "" || preg_match("/[^0-9]/", $mktime)) {
            return "";
        }
        return strftime("%Y-%m-%d %H:%M:%S", $mktime);
    }

    public static function GetDateMk($mktime) {
        if ($mktime == "" || preg_match("/[^0-9]/", $mktime)) {
            return "";
        }
        return strftime("%Y-%m-%d", $mktime);
    }

    //计算时间差
    public static function get_lasttime($lasttime) { //$lasttime 为数字时间
        if (strstr($lasttime, ':')) {
            $lasttime = strtotime($lasttime);
        }
        $l_str = "";
        $l_hour = 0;
        $l_minute = 0;
        $l_day = 0;
        $l_month = 0;
        $l_year = 0;
        $max_time = 24 * 60 * 60 * 30 * 12 * 2; //2年前不计算
        $l_s = time() - $lasttime; //距离(秒)
        // 超过一段时间，则不统计时间距离
        if ($l_s > $max_time)
            return date("Y-m-d H:i:s", $lasttime);
        //几分钟前
        if ($l_s > 60) {
            $l_minute = intval($l_s / 60);
            $l_sec2 = $l_minute % 60;
        }
        //几小时前
        if ($l_minute > 60) {
            $l_hour = intval($l_minute / 60);
            $l_minutes2 = $l_minute % 60;
        }
        //几天前
        if ($l_hour > 24) {
            $l_day = intval($l_hour / 24);
            $l_hour2 = $l_hour % 24;
        }
        //几个月前
        if ($l_day > 30) {
            $l_month = intval($l_day / 30);
            $l_day2 = $l_day % 30;
        }
        //几年前
        if ($l_month > 12) {
            $l_year = intval($l_month / 12);
            $l_month2 = $l_month % 12;
        }
        if ($l_year)
            return $l_year . "年" . ($l_month2 ? $l_month2 . "月" : '') . "前";
        if ($l_month)
            return $l_month . "个月" . ($l_day2 ? $l_day2 . "天" : '') . "前";
        if ($l_day)
            return $l_day . "天" . ($l_hour2 ? $l_hour2 . "时" : '') . "前";
        if ($l_hour)
            return $l_hour . "小时" . ($l_minutes2 ? $l_minutes2 . "分" : '') . "前";
        if ($l_minute)
            return $l_minute . "分钟" . ($l_sec2 ? $l_sec2 . "秒" : '') . "前";
        if (!$l_str)
            return ($l_s % 60) . "秒前";
    }

    //得到浏览者机型
    public static function getDeviceType() {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $type = 'ios';
        }
        if (strpos($agent, 'android')) {
            $type = 'android';
        }
        return $type;
    }

    public static function SpHtml2Text($str) {
        $str = preg_replace("/<sty(.*)\\/style>|<scr(.*)\\/script>|<!--(.*)-->/isU", "", $str);
        $alltext = "";
        $start = 1;
        $i = 0;
        for (; $i < strlen($str); ++$i) {
            if ($start == 0 && $str[$i] == ">") {
                $start = 1;
            } else if ($start == 1) {
                if ($str[$i] == "<") {
                    $start = 0;
                    $alltext .= " ";
                } else if (31 < ord($str[$i])) {
                    $alltext .= $str[$i];
                }
            }
        }
        $alltext = str_replace("　", " ", $alltext);
        $alltext = preg_replace("/&([^;&]*)(;|&)/", "", $alltext);
        $alltext = preg_replace("/[ ]+/s", " ", $alltext);
        return $alltext;
    }

    public static function Spcnw_mid($str, $start, $slen) {
        $str_len = strlen($str);
        $strs = array();
        $i = 0;
        for (; $i < $str_len; ++$i) {
            if (128 < ord($str[$i])) {
                if ($i + 1 < $str_len) {
                    $strs[] = $str[$i] . $str[$i + 1];
                } else {
                    $strs[] = "";
                }
                ++$i;
            } else {
                $strs[] = $str[$i];
            }
        }
        $wlen = count($strs);
        if ($wlen < $start) {
            return "";
        }
        $restr = "";
        $startdd = $start;
        $enddd = $startdd + $slen;
        $i = $startdd;
        for (; $i < $enddd; ++$i) {
            if (!isset($strs[$i])) {
                break;
            }
            $restr .= $strs[$i];
        }
        return $restr;
    }

    public static function GetAlabNum($fnum) {
        $nums = array("０", "１", "２", "３", "４", "５", "６", "７", "８", "９");
        $fnums = "0123456789";
        $i = 0;
        for (; $i <= 9; ++$i) {
            $fnum = str_replace($nums[$i], $fnums[$i], $fnum);
        }
        $fnum = ereg_replace("[^0-9\\.]|^0{1,}", "", $fnum);
        if ($fnum == "") {
            $fnum = 0;
        }
        return $fnum;
    }

    public static function Text2Html($txt) {
        $txt = str_replace("  ", "　", $txt);
        $txt = str_replace("<", "&lt;", $txt);
        $txt = str_replace(">", "&gt;", $txt);
        $txt = preg_replace("/[\r\n]{1,}/isU", "<br/>\r\n", $txt);
        return $txt;
    }
	
	public static function TextToHtml($txt) {
        $txt = preg_replace("/&amp;/isU", "&", $txt);
        $txt = preg_replace("/&amp;/isU", "&", $txt);
        $txt = preg_replace("/&lt;/isU", "<", $txt);
        $txt = preg_replace("/&gt;/isU", ">", $txt);
        $txt = preg_replace("/&quot;/isU", '"', $txt);
        return $txt;
    }	

    public static function ClearHtml($str) {
        $str = str_replace("<", "&lt;", $str);
        $str = str_replace(">", "&gt;", $str);
        return $str;
    }

    public static function cn_substr($str, $slen, $startdd = 0) {
        $restr = "";
        $c = "";
        $str_len = strlen($str);
        if ($str_len < $startdd + 1) {
            return "";
        }
        if ($str_len < $startdd + $slen || $slen == 0) {
            $slen = $str_len - $startdd;
        }
        $enddd = $startdd + $slen - 1;
        $i = 0;
        for (; $i < $str_len; ++$i) {
            if ($startdd == 0) {
                $restr .= $c;
            } else if ($startdd < $i) {
                $restr .= $c;
            }
            if (128 < ord($str[$i])) {
                if ($i + 1 < $str_len) {
                    $c = $str[$i] . $str[$i + 1];
                }
                ++$i;
            } else {
                $c = $str[$i];
            }
            if ($enddd <= $i) {
                if ($slen < strlen($restr) + strlen($c)) {
                    break;
                } else {
                    $restr .= $c;
                    break;
                }
            }
        }
        return $restr;
    }

    public static function cnSubStr($string, $start = 0, $length = null) {
        if (strlen($string) <= $length) {
            return $string;
        }
        $count = 0;
        $k = $start;
        if ($start != 0 && 127 < ord($string[$k])) {
            for (; 0 < $k; --$k) {
                if (ord($string[$k]) < 127) {
                    break;
                }
                ++$count;
            }
            if ($count % 2 == 0) {
                ++$start;
            }
            if (0 < $length) {
                --$length;
            }
        }
        $s = "";
        $count = 0;
        $i = $start;
        for (; $i < strlen($string); ++$i) {
            if (127 < ord($string[$i])) {
                $s .= $string[$i] . $string[++$i];
                $count += 2;
            } else {
                $s .= $string[$i];
                ++$count;
            }
            if (!empty($length) && $length < $count) {
                break;
            }
        }
        return $s;
    }

    public static function PutCookie($key, $value, $kptime, $pa = "/") {
        global $cfg_cookie_encode;
        global $cfg_pp_isopen;
        global $cfg_basehost;
        if ($cfg_pp_isopen == "0" || !ereg("\\.", $cfg_basehost) || !ereg("[a-zA-Z]", $cfg_basehost)) {
            setcookie($key, $value, time() + $kptime, $pa);
            setcookie($key . "ckMd5", substr(md5($cfg_cookie_encode . $value), 0, 16), time() + $kptime, $pa);
        } else {
            $dm = eregi_replace("http://([^\\.]*)\\.", "", $cfg_basehost);
            $dm = ereg_replace("/(.*)", "", $dm);
            setcookie($key, $value, time() + $kptime, $pa, $dm);
            setcookie($key . "ckMd5", substr(md5($cfg_cookie_encode . $value), 0, 16), time() + $kptime, $pa, $dm);
        }
    }

    public static function DropCookie($key) {
        global $cfg_cookie_encode;
        global $cfg_pp_isopen;
        global $cfg_basehost;
        if ($cfg_pp_isopen == "0" || !ereg("\\.", $cfg_basehost) || !ereg("[a-zA-Z]", $cfg_basehost)) {
            setcookie($key, "", time() - 3600000, "/");
            setcookie($key . "ckMd5", "", time() - 3600000, "/");
        } else {
            $dm = eregi_replace("http://([^\\.]*)\\.", "", $cfg_basehost);
            $dm = ereg_replace("/(.*)", "", $dm);
            setcookie($key, "", time(), "/", $dm);
            setcookie($key . "ckMd5", "", time(), "/", $dm);
        }
    }

    public static function GetCookie($key) {
        global $cfg_cookie_encode;
        if (!isset($_COOKIE[$key]) || !isset($_COOKIE[$key . "ckMd5"])) {
            return "";
        } else if ($_COOKIE[$key . "ckMd5"] != substr(md5($cfg_cookie_encode . $_COOKIE[$key]), 0, 16)) {
            return "";
        } else {
            return $_COOKIE[$key];
        }
    }

    //过滤文本中的标签
    public static function Gettext($str) {
        return preg_replace("/<(.*?)>/", "", htmlspecialchars_decode(stripslashes($str)));
    }

    //过滤文本中的标签
    public static function GetImgByText($str) {
        preg_match_all('/<img.*?>/im', $str, $match);

        //函数为完成，等待修复；
        $ret = Array();
        $i = 0;
        foreach ($match as $key => $val) {

            foreach ($val as $k => $v) {
                //标签全部变成小写
                //在scr=处打断字串
                $arr = explode('src=', strtolower($v));
                //在空格处打断
                $arr = explode(' ', strtolower($arr[1]));
                $ret[$i] = $arr[0];
                $i++;
            }
        }
        return $ret;
    }

    //读取指定的文件，并返回字符串
    public static function readFile($filepath, $len = '') {
        if (file_exists($filepath)) {
            $fp = fopen($filepath, 'r');
            $len = ($len == '') ? filesize($filepath) : $len;
            $str = fread($fp, $len);
            fclose($fp);
        } else {
            $str = '';
        }
        return $str;
    }

    //加密权限
    public static function powerHash($powerList) {
        return md5(md5($powerList) . POWER_CHECK);
    }

    //校验权限hash
    public static function checkPowerHash($powerList, $powerHash) {
        $powerList = is_null($powerList) ? '' : $powerList;
        return $powerHash === F::powerHash($powerList);
    }

    //获取我的权限
    public static function getMyPower($powerIdStr, $powerStr, $type) {
        if (F::powerHash($powerIdStr) !== $powerStr) {
            die('权限配置被篡改');
        }
        if (!strstr($powerIdStr, '|')) {
            die('权限配置错误,没有|');
        }
        $powerArray = explode('|', $powerIdStr);
        $powerArray = $powerArray[$type - 1];
        $powerArray = trim($powerArray, ',');
        return $powerArray;
    }

    //去掉数字中的重复值
    public static function noRepeatNum($numStr, $node = ',') {
        $numArray = explode(",", $numStr);
        $numArray = array_unique($numArray);
        $numStr = join($numArray, ",");
        return $numStr;
    }

    //统计点击是否有效
    public static function checkHit($sessionName = 'action', $sid = 1) {
        $oldSession = trim($_SESSION[$sessionName]);
        $oldSession = trim($oldSession, ",");
        $exist = false;
        if ($oldSession && strlen($oldSession) > 0) {
            if (strstr("," . $oldSession . ",", "," . $sid . ",")) {
                $exist = true;
            }
        }
        //写入session
        if (!$exist) {
            $newSession = $oldSession . "," . $sid;
            $newSession = trim($newSession, ",");
            $_SESSION[$sessionName] = $newSession;
        }
        return $exist;
    }

    //创建多级文件夹
    public static function creatdir($path) {
        if (!is_dir($path)) {
            if (F::creatdir(dirname($path))) {
                mkdir($path, 0777);
                return true;
            }
        } else {
            return true;
        }
    }
	
    //将索引-主键 替换竖线 并且 加引号
    public static function addYh($ids) {
        $ids = str_replace('|', ',', $ids);
        $ids = str_replace("'", "", $ids); //先去掉单引号,防止多加引号
        $ids = trim($ids, ",");
        $ids = trim($ids, ",");
        $ids = str_replace(",", "','", $ids);
        $ids = trim($ids, "'");
        $ids = trim($ids, "'");
        $ids = "'" . $ids . "'";
        return $ids;
    }
	
    //将索引-主键 数组转字符串 加引号
    public static function arrYh($ids = []) {
		$ids = implode('|', $ids);		
        return self::addYh($ids);
    }

//根据淘宝空间、阿里云OSS图片来源设置不同的图片后缀
    public static function adjustImg($url, $width, $height) {
        if (preg_match('/^http:\/\/image\.yikuaiyou\.com\/[\w]+\/[\w]+\/[\d]+\/[\w]+\.(jpg|png|gif)$/i', $url)) {
            return $url .= '@' . $width . 'w_' . $height . 'h.jpg';
        }
        if (preg_match('/.*@[\d]+w_[\d]+h\.(jpg|png|gif)$/i', $url, $match)) {
            $url = preg_replace('/@[0-9]+w_[0-9]+h\.[jpg|png]/i', '', $match[0]);
            return $url .= '@' . $width . 'w_' . $height . 'h.jpg';
        }
        if (preg_match('/^http:\/\/(.*)_[\d]+x[\d]+\.(jpg|png|gif)$/i', $url, $match)) {
            $url = 'http://' . $match[1] . '_' . $width . 'x' . $height . '.jpg';
            return $url;
        } else {
            return $url .= '_' . $width . 'x' . $height . '.jpg';
        }
    }

    //格式化数字类型输出，主要用于处理在数据库中取出的结果集
    public static function fmtNum($var) {
        if (is_numeric($var)) {
            return $var = is_int($var) ? (int) $var : (float) $var;
        }
        return 0;
    }

    //内存使用情况
    public static function memory_usage() {
        $memory = (!function_exists('memory_get_usage')) ? '0' : round(memory_get_usage() / 1024 / 1024, 2) . 'MB';
        return $memory;
    }

    //读出attrib表中指定项目,并处理成可转Options格式的数组,最多读两级,所以属性表中就不要超过两级了
    public static function getAttrs($type, $group = false) {//$type:属性类型;$group:格式化输出
        $cache = new cache();
        $cacheId = 'attrs_' . $type . '_' . var_export($group, true);
        $cacheAttrs = $cache->get($cacheId);
        if ($cacheAttrs) {
            return $cacheAttrs;
        } else {
            $db = new MySql();
            $sql = "select at_key, at_value, at_fkey from `tang_attrib` where at_type = '" . $type . "' order by at_key asc";
            $attrs = $db->getAll($sql);

            if ($group) {
                $newAttrs = array();
                foreach ($attrs as $att) {
                    $f = $att['at_fkey'];
                    $key = $att['at_key'];
                    $val = $att['at_value'];
                    if ($f == 0) {
                        $newAttrs[$key]['label'] = $val;
                    } else {
                        $newAttrs[$f]['options'][$key] = $val;
                    }
                }
                $cache->set($cacheId, $newAttrs);
                return $newAttrs; //self::array2Options($newAttrs, [], true);
            } else {
                $attrs = array_column($attrs, 'at_value', 'at_key');
                $cache->set($cacheId, $attrs);
                return $attrs;
            }
        }
    }

    //数组转Options(optgroup)
    public static function array2Options($optsArray, $selected = array(), $group = false) {
        $optionsTemp = '';
        foreach ($optsArray as $key => $val) {
            if (is_array($val) && $group) {
                $optionsTemp .= '<optgroup label="' . $val['label'] . '">';
                foreach ($val['options'] as $k => $v) {
                    $optionsTemp .= '<option value="' . $k . '"';
                    $optionsTemp .= (in_array($k, $selected)) ? ' selected="selected"' : '';
                    $optionsTemp .= '>' . $v . '</option>';
                }
                $optionsTemp .= '</optgroup>';
            } else {
                $optionsTemp .= '<option value="' . $key . '"';
                $optionsTemp .= (in_array($key, $selected)) ? ' selected="selected"' : '';
                $optionsTemp .= '>' . $val . '</option>';
            }
        }
        return $optionsTemp;
    }

    //生成认证的图片的名称
    public static function authImageName($id, $type) {
        return md5(md5($id) . POWER_CHECK . $type);
    }

    //获取认证的图片的路径 F::authImageExist($uid, 3);return 1:返回完整网址;2:返回文件路径
    public static function authImageExist($id, $type, $return = 1) {
        $fileName = md5(md5($id) . POWER_CHECK . $type);
		
        $filePath = WEBROOT . '/app/u/upload/image' . '/' . $fileName;
        $root = ($return == 1) ? BASEURL : WEBROOT;
		$img = '';
        if (file_exists($filePath . '.jpg')) {
            $img = $root . '/app/u/upload/image' . '/' . $fileName . '.jpg';
        }
        if (file_exists($filePath . '.png')) {
            $img = $root . '/app/u/upload/image' . '/' . $fileName . '.png';
        }
        if (file_exists($filePath . '.gif')) {
            $img = $root . '/app/u/upload/image' . '/' . $fileName . '.gif';
        }
		if($img == ''){
			return false;
		}else{
			$img .= ($return == 1) ? '?t='. time() : '';
			return $img;
		}
    }
    
    /**
     * 四舍六入偶除余-乘法
     */
    public static function bankerAlgorithm($money,$num,$decimal=3) {
        return round(bcmul($money, $num, $decimal), ($decimal - 1), PHP_ROUND_HALF_EVEN);
    }
	
    /**
     * 四舍六入偶除余-除法
     */
    public static function bankerDIv($money,$num,$decimal=3) {
        return round(bcdiv($money, $num, $decimal), ($decimal - 1), PHP_ROUND_HALF_EVEN);
    }
	
	//根据最后一级行业分类取出完整行业路径 $id:行业分类的code;$return: 1-返回数组,2-返回字符串;$split: 拼接数组的字符串,仅$return=2时生效;
	public static function getFullInd($id = '', $return = 1, $split = ' &gt; '){
		$db = new MySql();
		$sql = "SELECT ind_id, ind_code, ind_name, ind_fkey, ind_gdp FROM `t_industry` WHERE TRIM(TRAILING '00' FROM ind_code) = SUBSTR('".$id."', 1, LENGTH(TRIM(TRAILING '00' FROM ind_code))) ORDER BY ind_code ASC";
		$Inds = $db->getAll($sql);
		if($Inds){
			$_Inds = '';
			foreach($Inds as $v){
				$_Inds[] = array(
					'id' => $v['ind_id'],
					'code' => $v['ind_code'],
					'name' => $v['ind_name'],
					'fkey' => $v['ind_fkey'],
					'gdp' => $v['ind_gdp'],
				);
			}
			if($return == 1){//返回数组
				return $_Inds;
			}else{
				return implode($split, array_column($_Inds, 'name'));
			}
		}else{//如果没有,返回空值
			return ($return == 1) ? [] : '';
		}
	}
	
	//根据最后一级行政区划取出完整行政区划路径 $id:行政区划的code;$return: 1-返回数组,2-返回字符串;$split: 拼接数组的字符串,仅$return=2时生效;
	public static function getFullArea($id = '', $return = 1, $split = ' &gt; '){
		$db = new MySql();		
		$sql = "SELECT a_id, a_code, a_name, a_fkey, a_gdp FROM `t_area` WHERE TRIM(TRAILING '00' FROM a_code) = SUBSTR('".$id."', 1, LENGTH(TRIM(TRAILING '00' FROM a_code))) ORDER BY a_code ASC";
		$areas = $db->getAll($sql);
		if($areas){
			$_areas = '';
			foreach($areas as $v){
				$_areas[] = array(
					'id'   => $v['a_id'],
					'code' => $v['a_code'],
					'name' => $v['a_name'],
					'fkey' => $v['a_fkey'],
					'gdp'  => $v['a_gdp'],
				);
			}
			if($return == 1){//返回数组
				return $_areas;
			}else{
				return implode($split, array_column($_areas, 'name'));
			}
		}else{//如果没有,返回空值
			return ($return == 1) ? [] : '';
		}
	}
        
        static public function phpinfo(){
            phpinfo();
        }

	/**
	* 获取服务器端IP地址
	 * @return string
	 */
	function getServerIp() { 
		if (isset($_SERVER)) { 
			if($_SERVER['SERVER_ADDR']) {
				$server_ip = $_SERVER['SERVER_ADDR']; 
			} else { 
				$server_ip = $_SERVER['LOCAL_ADDR']; 
			} 
		} else { 
			$server_ip = getenv('SERVER_ADDR');
		} 
		return $server_ip; 
	}

	/* 分表之后,如果取得是两个月之前的数据需要判断表名称 */
	function getTable($options, $table, $field){
		if(isset($options['search'])){
			
			if(isset($options['search'][$field])){
				/* 异动编号 */
				if(isset($options['search'][$field]['value']) && strlen($options['search'][$field]['value']) == 25){
					$date = F::verifyDateTime(substr($options['search'][$field]['value'], 0, 8), 'Ymd');
					if($date){
						$date = $date->format('U');
						//$date = strtotime($date->date);
					}else{
						$date = F::mytime();
					}
				}
				/* 日期范围检索 */
				else{	
					if(count($options['search'][$field]) == 2){//有开始时间和结束时间
						$date = strtotime($options['search'][$field][0]['value']);		
					}else{
						$date = strtotime($options['search'][$field]['value']);
					};
				}
				
				$y = date('Y', $date);
				$m = date('m', $date);
				$yn = F::mytime('Y');
				$mn = F::mytime('m');
				if((($yn - $y) * 12 + ($mn - $m)) > 1){//两个月以前,要查历史表
					$table .= '_'.date('Ym', $date);
					$db = new MySql();
					if(!$db->isTable($table)){
						return false;
					}
				}				
			};			
		}
		return $table;		
	}
	
    public static function isMobile()
    { 
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
		$is_mobile = false;
		foreach ($mobile_agents as $device) {
			if (stristr($user_agent, $device)) {
				$is_mobile = true;
				break;
			}
		}
		return $is_mobile;
    } 
	
	/* datatable输出空数据 */
    public static function dtEmpty(){
	   return json_encode(array(
			'draw' => 0,
			'recordsTotal' => 0,
			'recordsFiltered' => 0,
			'data' => [],			
		));
    }
	
    /**
     * 跟系统的http_build_str()功能相同，但不用安装pecl_http扩展
     * author ranqin
     * @param array     $array      需要组合的数组
     * @param string    $separator  连接符
     * @return string               连接后的字符串
     * eg: 举例说明
     */
    public function http_build_string($array, $separator='&'){
        $string = '';
        foreach($array as $key=>$val){
            $string .= "{$key}={$val}{$separator}";
        }
        //去掉最后一个连接符
        return substr($string, 0, strlen($string) - strlen($separator));
    }
		
    /*获取联盟商家产生的合同编号*/
    public static function getContractCode(){
        $db = new MySql();
        $sql = "SELECT max(uc_contractCode) FROM t_union_companyex WHERE 1=1 LIMIT 1";
        $contractCodeAry = $db->getField($sql);
        /*获取合同编码*/
        $oldContractCode = !empty($contractCodeAry) ? end(explode('-',$contractCodeAry)):0;
        /*合同编号增加一*/
        $contractCode = intval($oldContractCode) + 100000001;
        $newContractCode = "DT-LMSJ-".$contractCode;//新合同编码
        $newContractCode = str_replace('-1','-',$newContractCode);
        return $newContractCode;

    }
	
	/** 
	 * 人民币小写转大写 
	 * 
	 * @param string $number 数值 
	 * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆" 
	 * @param bool $is_round 是否对小数进行四舍五入 
	 * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30， 
	 *             有的系统要求输出"壹仟玖佰陆拾元零叁角"，实际上"壹仟玖佰陆拾元叁角"也是对的 
	 * @return string 
	 */ 
	function num2rmb($number = 0, $int_unit = '元', $is_round = TRUE, $is_extra_zero = FALSE) 
	{ 
		// 将数字切分成两段 
		$parts = explode('.', $number, 2); 
		$int = isset($parts[0]) ? strval($parts[0]) : '0'; 
		$dec = isset($parts[1]) ? strval($parts[1]) : ''; 
		
		// 如果小数点后多于2位，不四舍五入就直接截，否则就处理 
		$dec_len = strlen($dec); 
		if (isset($parts[1]) && $dec_len > 2) 
		{ 
			$dec = $is_round 
			? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1) 
			: substr($parts[1], 0, 2); 
		} 
		
		// 当number为0.001时，小数点后的金额为0元 
		if(empty($int) && empty($dec)) 
		{ 
			return '零'; 
		} 
		
		// 定义 
		$chs = array('0','壹','贰','叁','肆','伍','陆','柒','捌','玖'); 
		$uni = array('','拾','佰','仟'); 
		$dec_uni = array('角', '分'); 
		$exp = array('', '万'); 
		$res = ''; 
		
		// 整数部分从右向左找 
		for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++) 
		{ 
			$str = ''; 
			// 按照中文读写习惯，每4个字为一段进行转化，i一直在减 
			for($j = 0; $j < 4 && $i >= 0; $j++, $i--) 
			{ 
				$u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位 
				$str = $chs[$int{$i}] . $u . $str; 
			} 
			//echo $str."|".($k - 2)."<br>"; 
			$str = rtrim($str, '0');// 去掉末尾的0 
			$str = preg_replace("/0+/", "零", $str); // 替换多个连续的0 
			if(!isset($exp[$k])) 
			{ 
				$exp[$k] = $exp[$k - 2] . '亿'; // 构建单位 
			} 
			$u2 = $str != '' ? $exp[$k] : ''; 
			$res = $str . $u2 . $res; 
		} 
		
		// 如果小数部分处理完之后是00，需要处理下 
		$dec = rtrim($dec, '0'); 
		
		// 小数部分从左向右找 
		if(!empty($dec)) 
		{ 
			$res .= $int_unit; 
			
			// 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求 
			if ($is_extra_zero) 
			{ 
				if (substr($int, -1) === '0') 
				{ 
					$res.= '零'; 
				} 
			} 
			
			for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++) 
			{ 
				$u = $dec{$i} > 0 ? $dec_uni[$i] : ''; // 非0的数字后面添加单位 
				$res .= $chs[$dec{$i}] . $u; 
			} 
			$res = rtrim($res, '0');// 去掉末尾的0 
			$res = preg_replace("/0+/", "零", $res); // 替换多个连续的0 
		} 
		else 
		{ 
			$res .= $int_unit . '整'; 
		} 
		return $res; 
	} 	
	
}

// 浏览器友好的变量输出
function dump($var, $echo = true, $label = null) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    $output = print_r($var, true);
    $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
    if ($echo) {
        echo($output);
        return null;
    } else
        return $output;
}

/**
 * 对象转换成数组
 */
function object_to_array($obj) {
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}
//模板包含
//模板采取缓冲模式，只有在修改模板文件时候才更新缓冲
function includeTemplet($path,$iscache=true,$vars=[]) {
	if(!isset($path))
		return false;
	$path = trim($path);	
    $level = count(explode('/',$path));
	if($level == 1) 
	   $p = PATH_TEMPLATE;
	elseif($level == 2)
	   $p = dirname(PATH_TEMPLATE);  
	else
	   return false;   
	$filePath = $p.DIRECTORY_SEPARATOR.$path.'.html';
	if(!file_exists($filePath)) {
		trigger_error('templet "'.$filePath.'" not exist',E_USER_ERROR);
	}
	if(!$iscache) {
	   $content = F::readFile($filePath);
	   $content = myHTML::analysisFunc($content,$vars);
	   return $content;
	}
	$sign = md5($filePath.(!empty($vars) ? implode('',$vars) : ''));
	$cache = new cache();
	if($templentInfo = $cache->get($sign)) {
		$edittime =filemtime($filePath);  //获取文件更新时间
		$tempArr = unserialize($templentInfo);
		$lastCacheTime = $tempArr['lastcachetime']; //最后一次缓冲的时间
		if($edittime < $lastCacheTime) {
		  return $tempArr['content'];		
		}	
	}
	$content = F::readFile($filePath);
	$content = myHTML::analysisFunc($content,$vars);
	$tempArr['lastcachetime'] = time();
	$tempArr['content'] = $content;
	$cache->set($sign,serialize($tempArr), 60*60*24*360);
	return $content;	
  }

	// 假设当前域名为www.a.com
	// 当$url == 'u/index/read'时候url拼接成http://u.a.com/index/read
	// 当$url == '/index/read'时候url拼接成http://www.a.com/index/read
	// 当$url == 'index'时url拼接成http://www.a.com/index
	// $vars为数组或字符串 如果为array('id'=>1,'a'=>6) 生成id=1&a=6
	//$returnHosts=1 强制返回全域名
	//在U项目中: U('/u/index/index', [], 1); 返回:http://u.a.com/index/index; 2:返回ajax加载链接
   function U($url='', $vars='', $returnHosts = 0) {
	 $curUrl = F::GetCurUrl();
	 $urlInfo   =  parse_url($url);	
	 $curUrlInfo = parse_url($curUrl);
     $path    =  trim(!empty($urlInfo['path']) ? $urlInfo['path'] : $curUrlInfo['path'],'/');
	 $postion = strpos($path,'/');
	 if(false !== $postion) { // 解析域名和action
	        $pathArr    =   explode('/',$path);
			if(count($pathArr) == 3) {
				$first = $pathArr[0];
				$tempArr = explode('.',$first); //如果是u.a.com/index/read情况
			    if(count($tempArr) == 3) {
			      $host = $first; 	
			    }else{
			      $host = $first.substr($curUrlInfo['host'],strpos($curUrlInfo['host'],'.')); //该情况当$url =='u/index/read'时候会转化成u.a.com/index/read则$host=='u.a.com'
			    }
				$model = $pathArr[1].'/'.$pathArr[2];
			}elseif(count($pathArr) == 2){
				$host = $curUrlInfo['host'];
				$model = $pathArr[0].'/'.$pathArr[1];
			}
     }else{
		 $tempArr = explode('.',$path);
		 if(count($tempArr) == 3) {
			  $host = $path;
			  $model = ''; 	
		 }else{
			  $host = $curUrlInfo['host'];
		      $model = $path;
		 }
	 }
	 
	  
	 if(is_string($vars)) 
		 $query = $vars;
	 elseif(is_array($vars))
		 $query = http_build_query($vars);
	 else
		 $query = '';
	 if($query =='') {
		 $query = empty($urlInfo['query']) ? '' : $urlInfo['query'];
	 }else{
		 $query = empty($urlInfo['query']) ? $query : $urlInfo['query'].'&'.$query;
	 }
	 if(isset($urlInfo['fragment'])) { //锚点
        $anchor =   $urlInfo['fragment'];
	 }elseif(is_string($vars) && $p = strpos($vars,'#')){
		$anchor = substr($vars,$p+1); 
	 }else{
		$anchor = ''; 
	 }
	 
	  
	 if(isset($urlInfo['scheme'])) {
		$scheme = $urlInfo['scheme'];
	 }elseif(strstr($host,'.') == strstr($curUrlInfo['host'],'.')){
		$scheme = $curUrlInfo['scheme'];
	 }else{
		$scheme = 'http'; 
	 }
	
	 $request_url = '/'.$model.($query!='' ? '?'.$query : '').($anchor!='' ? '#'.$anchor : '');
	 
	 $request_url = ($returnHosts == 2) ? '#'.$request_url : $request_url;
	 
	 if($host != $curUrlInfo['host'] || $returnHosts == 1) {
		 return $scheme.'://'.$host.$request_url; 
	 }else{
		 return $request_url;
	 }	 
   }

    function  L($id) {
	   return !isset($id) ? '' : message::getMessageByID($id);
    }
	
	//生成随机数
	function getRandChar($length=6){
      $str = null;
      $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
      $max = strlen($strPol)-1;
      for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
      }
      return $str;
    }
	
	function getCacheFileContent($path,$sign='') {
		if(empty($path) || !is_file($path)) {
			return '';
		}
		$sign = !empty($sign) ? $sign : md5($path);
	    $cache = new cache();
	    if($templentInfo = $cache->get($sign)) {
		  $edittime =filemtime($path);  //获取文件更新时间
		  $tempArr = unserialize($templentInfo);
		  $lastCacheTime = $tempArr['lastcachetime']; //最后一次缓冲的时间
		  if($edittime < $lastCacheTime) {
		    return $tempArr['content'];		
		  }	
	    }
		$content = F::readFile($path);
	    $tempArr['lastcachetime'] = time();
	    $tempArr['content'] = $content;
	    $cache->set($sign,serialize($tempArr), 60*60*24*360);
	    return $content;	
	}	

	/**
	 * 连续输错5次安全密码，将在两个小时内不能使用支付帐户
	 * @param $userID        用户id
	 * @return int
	 */
	function checkAndSetPayPwdTimes($userID){
		$cache = new cache();
		$cacheId = md5($userID);
		$v = $cache->get($cacheId);
		$arr = array(
		//'times' => '',
		'msg' => '您的安全密码输入错误,如果安全密码连续错误5次，您的支付账户将被冻结2小时',
		);
		if ( empty( $v ) ) {
			$cache->set( $cacheId, array('errno' => 1), 2 * 60 * 60 );
			//$arr['times'] = 1;
			$arr['msg'] = '您的安全密码已输入错误1次,如果安全密码连续错误5次，您的支付账户将被冻结2小时';
		} elseif ( $v[ 'errno' ] < 4 ) {
			$cache->set( $cacheId, array('errno' => $v[ 'errno' ] + 1), 2 * 60 * 60 );
			//$arr['times'] = $v[ 'errno' ] + 1;
			$arr['msg'] = '您的安全密码已输入错误'.($v[ 'errno' ] + 1).'次,如果安全密码连续错误5次，您的支付账户将被冻结2小时';
		} else {
			$cache->set( $cacheId, array('errno' => $v[ 'errno' ] + 1), 2 * 60 * 60 );
			//$arr['times'] = $v[ 'errno' ] + 1;
			$arr['msg'] = '您输入的安全密码已连续错误5次或以上，您的支付账户已冻结2小时,请您2小时之后再进行操作';
		}
		return $arr;
	}

	function checkPayPwdTimes($userID){
		$cache = new cache();
		$cacheId = md5($userID);
		$v = $cache->get($cacheId);
		$arr = array(
		//'times' => '',
		'msg' => '',
		);
		if ( isset( $v[ 'errno' ] ) && $v[ 'errno' ] > 4 ) {
			//$arr['times'] = $v[ 'errno' ] + 1;
			$arr['msg'] = '您输入的安全密码已连续错误5次或以上，您的支付账户已冻结2小时,请您2小时之后再进行操作';
			return $arr;
		}
	}
	
   
?>
