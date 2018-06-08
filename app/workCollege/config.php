<?php

/**
 * @author flybug
 * @version 2.8.0
 * @date 2015-10-28
 * 系统配置参数
 */
//基本参数

define('PROJECTNAME', '工作平台');
define('LANGUAGE', 'cn'); //语言
define('BASEURL', 'http://' . WORKERURL); //系统的url头
define('SUBDOMAIN', WORKERURL); //域名
define('NAME', '工作平台'); //系统的名字
define('ADMINMAIL', ''); //admin的邮箱
define('VERSION', ''); //系统的版本
define('PAGESIZE', 20); //默认分页的大小
define('PASSKEYWORD', 'flybug_@'); //加密密钥
define('HOMEPAGE', '');
define('SESSIONLIFE', 86400);
define('MAXFORMTOKEN', 5); //同一个session最大的form会话数量
define('TIMEZONE', 'RPC'); //设置当前应用的系统时区
define('DEBUG', false); //定义运行模式
//SEO设置
define('SEO_TITLE', ''); //标题
define('SEO_KEYWORDS', ''); //关键字
define('SEO_DESCRIPTION', ''); //描述
//安全设置
define('SYS_NEEDFILTER', true); //是否需要字符过滤
define('SYS_REPLACESTRING', '操你|她妈|它妈|他妈|你妈|妈逼|妈B|fuck|去死|贱人|妈B|叼|擦|戳|CAO|TMD|尼玛|垃圾|屁|龌龊|SB|煞笔|傻B|祖宗|白痴|吃屎|香港占中|香港中环|香港民主|香港大学生');
define('SYS_NEEDCHECKADMINIP', true); //是否校验admin的登录ip
define('SYS_ADMINIP', ''); //admin的登录ip
define('SYS_NEEDCHECKWORKIP', true); //是否需要字符过滤
define('SYS_ROUTEROS', '[{"SERVER":"468804f84a27.sn.mynetname.net","MODE":"read","PWD":"789654123"},{"SERVER":"4688041bb39f.sn.mynetname.net","MODE":"read","PWD":"789654123"}]'); //routeros参数配置
define('SYS_WEBTABLE', '["tj.youpinshiyong.com","ju.youpinshiyong.com"]'); //平台子域名
//数据库参数
define('DB_TYPE', 'mysql');
define('DB_HOST', '192.168.3.203');
define('DB_PORT', '3306');
define('DB_NAME', 'tangCollegeDevelop');
define('DB_USER', 'root');
define('DB_PASSWORD', 'sql@8234ERe8');
define('DB_PREFIX', '');
define('DB_CHARSET', 'utf8');
define('DB_LOGS', 'mongodb');

//mongodb设置
define('MONGO_DSN', 'mongodb://tang:tang@192.168.3.205:27017/tang');
define('MONGO_DB', 'tang');


//redis设置
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASS', '!@#$%^&*()WERTYUIOSDFGHJKXCVBNM<OUY#456789rftgyui');

//memecache配置
define('MEMCACHE_OPEN', TRUE);
define('MEMCACHE_COMP', FALSE);
define('MEMCACHE_SERVERS', '127.0.0.1');
define('MEMCACHE_PORT', '11211');

//系统路径(相对路径)
define('PATH_LOG', APPROOT . '/log');
define('PATH_UPLOAD', APPROOT . '/upload');
define('PATH_CACHE', APPROOT . '/cache');
define('PATH_PHOTO', APPROOT . '/upload/photo');
define('PATH_AD', APPROOT . '/upload/ad');
define('PATH_GOODS', APPROOT . '/upload/goods');
define('PATH_IMAGE', APPROOT . '/upload/images');

//应用参数
define('APP_POWER', '{"b":"1,2,3,4,5,6,7,14,16,18,19,25","s":"1,2,3,4,5,6,7,9,10,11,12,16,18,25,","e":"1,5,6,7,16,18,44,45,46,62,63,55,66,67,68,69,70"}'); //用户组的权限（买家,卖家,雇员）
define('APP_SIGNSCORE', '1|2,2|3,3|4,4|5,5|6,6|7,7|8,8|8'); //签到积分设置（连续签到天数|获得积分）
//系统允许处理的文件类型
define('FILE_IMAGE', 'jpg|gif|png');
define('FILE_SOFT', 'exe|zip|gz|rar|iso|doc|xsl|ppt|wps');
define('FILE_MEDIA', 'swf|mpg|dat|avi|mp3|rm|rmvb|wmv|asf|vob|wma|wav|mid|mov');

//阿里云参数配置
define('ALIYUN_KEYID', 'FM4LRK667sr2tBQD');
define('ALIYUN_SECRET', 'HEgQxUgwSNZLt5GmzUsqsot0GBBhYE');
define('ALIYUN_OSSImageBucket', 'yikuaiyou-image');
define('ALIYUN_OSSMediaBucket', 'yikuaiyou-media');

//企业信使
define('SMS_USERID', '519');
define('SMS_KEYID', 'hndt');
define('SMS_SECRET', '0E156917215fcA');
define('SMS_TYPE', 2); //短信发送平台：0-电信天翼；1-短信网；2-企业信使
define('SMS_SENDINTERVAL', 180);


/* 私钥 */
//error_reporting(0);
define('SECRETKEY_PATH',WEBROOT.'/secretkey');
define('IGNORE_SIGN_APPS','college|workcollege|wapcollege');
