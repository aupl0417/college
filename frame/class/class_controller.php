<?php

/**
 *
 * 基类，所有控制器父类别
 *
 * 主要功能是实现了页面工厂框架模型；
 * 1、整合了显示部分的方法，统一用show方法统一处理原显示和执行。（2.0.0重要修改）；
 * 2、在基类增加了页面防篡改，防重复提交，身份标识等特性（3.0重要功能 2014-11-02）
 *
 * @author flybug
 * @version 3.0.0.0
 *
 */
class controller {

    protected $name; //类对象名称
    protected $cachepath = ''; //缓存目录
    protected $useformtoken = false; //使用form令牌
    protected $usecache = false; //需要全局输出缓存标志
    protected $cachetime = 3600; //全局输出缓存时间（单位：秒）
    protected $options = NULL; //页面参数数组
    protected $cuser = NULL; //当前合法用户
    protected $head = ''; //头部
    protected $foot = ''; //尾部
    protected $templatepath = ''; //模板路径
    protected $templatefile = ''; //组装中的模板(带物理路径的文件)
    protected $temp_data = null; //控制器数据
    protected $temp_html = ''; //html代码
    protected $head_title; //标题
    protected $head_keywords; //关键字
    protected $head_description; //描述
    protected $sign; //控制器标签（使用url路径+类名取md5，所有系统唯一）
    protected $runtime; //运行时间（单位：秒）
    protected $usemem; //使用内存
    protected $mypoint = false; //自定义断点输出运行时间和运行内存消耗
    protected $protocal = 'http://'; //http(s)协议

    /*
     * 控制器构建函数
     * $options：输入参数数组
     * $checkActer：身份校验（0-访客；1-会员；2-雇员；3-？）
     * $checkPowerID：需要校验的权限编码(传入的是权限id数组，可支持复合权限编码校验，eg：[2,3])
     * 备注：权限校验遵循先身份校验，其次个人校验，再组校验。
     * 即身份不对无法访问，如果身份正确，则从个人权限有则正确，个人权限没有但所在组权限有也正确，如果个人权限、组权限都没有，则无权限
     */

    public function __construct($options = '', $checkActer = [], $checkPowerID = []) {
        $this->name = $options['PATH_ACTION'];
        $this->options = $options;
        $this->sign = md5(APP_NAME . $options['PATH_MODEL'] . $options['PATH_ACTION']);
        //调试开关
        if (DEBUG) {
            $this->runtime = microtime(TRUE);
            $this->usemem = memory_get_usage();
        }
		
        //身份权限校验
        $this->checkPower($checkActer, $checkPowerID); //校验身份
		
        //校验提交令牌
        /*
         * form令牌是一个数组$_SESSION['formtoken']，最大限制为MAXFORMTOKEN，当大于限制时，先删除最旧的键值对，再尾部追加
         * 新测键值对。
         */
        if ($this->useformtoken) {
            if (!isset($options['_posttoken']) || $_SESSION['formtoken'][$options['_posttoken']] == 0) {
                echo message::getJsonMsgStruct('0007');
                exit;
            } else {
                $this->lockFormToken($options['_posttoken']); //锁token
            }
        }
        //强制刷新
        if (isset($options['flush'])) {
            $this->flush();
        }
        //缓存开关
        if ($this->usecache) {
            $cache = new cache();
            $this->temp_html = $cache->get($this->sign);
            if ($this->temp_html) {
                echo $this->temp_html;
                exit;
            }
        }
        //环境变量
        $this->cachepath = APPROOT . PATH_CACHE;
        $this->templatepath = PATH_TEMPLATE;
        $this->head_title = SEO_TITLE;
        $this->head_keywords = SEO_KEYWORDS;
        $this->head_description = SEO_DESCRIPTION;
		
		//http(s)
		$this->protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? $this->protocal : 'http://';
    }

    public function __destruct() {
        if (DEBUG && !$this->mypoint) {
            $this->getPoint();
        }
    }

    public function openCache($time = 3600) {
        $this->usecache = true;
        $this->cachetime = $time;
    }

    //设置资源使用量调试点
    public function setPoint() {
        $this->mypoint = true;
        $this->runtime = microtime(TRUE);
        $this->usemem = memory_get_usage();
    }

    //得到资源使用量
    public function getPoint() {
        $this->runtime = round(microtime(TRUE) - $this->runtime, 6);
        $this->usemem = round((memory_get_usage() - $this->usemem) / 1024, 6);
        echo '<br /><br />-------runing information----------------------------<br />';
        echo sprintf('APPNAME:%s,PATH_MODEL:%s,PATH_ACTION:%s <br />', APP_NAME, $this->options['PATH_MODEL'], $this->options['PATH_ACTION']);
        $this->addDebugInfo('sign', $this->sign);
        $this->addDebugInfo('runtime', $this->runtime . ' s');
        $this->addDebugInfo('usemem', $this->usemem . ' kb');
    }

    public function addDebugInfo($k, $v) {
        echo $k . '：' . $v . '<br />';
    }

    //设置页面head标签中的描述 tag: title/keywords/description
    public function setHeadTag($tag, $str) {
        $this->{"head_$tag"} = $str;
        return $this;
    }

    //设置模板文件
    public function setTemplateFile($tempfile = '') {
//        die($this->templatepath);
        $this->templatefile = ($tempfile == '') ? "{$this->templatepath}/{$this->name}.html" : "{$this->templatepath}/$tempfile.html";
        $this->getHtmlFromTemplateFile();
        return $this;
    }

    //设置数据
    public function setTemplateData($data = []) {
        if ($data != []) {
            $this->temp_data = $data;
        }
        return $this;
    }

    //设置模版和数据
    public function setTempAndData($tempfile = '', $data = []) {
        $this->setTemplateData($data);
        $this->setTemplateFile($tempfile); //设置模板
        return $this;
    }

    //添加replace模版数据
    public function setReplaceData($k, $v = '') {
        if (!isset($this->temp_data['_replace'])){
            $this->temp_data['_replace'] = array();
        }
        if (is_array($k)) {
            $this->temp_data['_replace'] = array_merge($this->temp_data['_replace'], $k);
        } else {
            $this->temp_data['_replace'][$k] = $v;
        }
        return $this;
    }

    //添加loop模版数据
    public function setLoopData($k, $v) {
        $this->temp_data['_loop'][$k] = $v;
        return $this;
    }

    //设置页面合法标签
    public function setTempSign() {
        $sign = F::getGID();
        if (preg_match('/\<\/body\>/i', $this->temp_html)) {
            $this->temp_html = preg_replace('/\<\/body\>/i', "<div name = '_sign' sign='$sign'></div></body>", $this->temp_html);
        } else {
            $this->temp_html .= "<div name = '_sign' sign='$sign'></div>";
        }
/* 		dump(strlen($this->temp_html));
		dump(strlen($this->temp_html));
		die; */
        return $this;
    }

    //设置使用form令牌
    public function useFormToken($falg = true) {
        $this->useformtoken = true;
    }

    /*
     * 设置form令牌
     * 页面form表单会话，是为了防止重复提交而设计的，在$_SESSION['formtoken']内存储token和状态值的键值对，每次生成表单
     * 时候，自动生成一个token键，对应状态为有效1，为了防止恶意刷新页面，造成此键值对数组过长，设置了数组最大长度，实现了
     * 队列的先进先出，维护定长的formtoken数组
     */

    public function setFormToken() {
        $sign = F::getGID();
		//unset($_SESSION['formtoken']);
		!isset($_SESSION['formtoken']) && $_SESSION['formtoken'] = '';
	

        if (count($_SESSION['formtoken']) > MAXFORMTOKEN) {
            //超过了最大formtoken，删除最旧的一个token，即数组的第一个元素。
            array_shift($_SESSION['formtoken']);
        }
        //尾部追加一个token，状态为有效1。
        $_SESSION['formtoken'][$sign] = 1;
        //dump($this->temp_html);

        if (preg_match_all('/\<form\s+action.+?\>(\s|.)+?(?=\<\/form\>)/i', $this->temp_html, $matchForms)) {
			foreach($matchForms[0] as $match){				
				$this->temp_html = str_replace($match, $match."<input id = '_posttoken' name = '_posttoken' type='hidden' value='$sign'>", $this->temp_html);
			};			
           //$this->temp_html = preg_replace('/<form>/i', "<form><input id = '_posttoken' name = '_posttoken' type='hidden' value='$sign'>", $this->temp_html);
        } elseif (preg_match('/<body[^>]*?>/i', $this->temp_html)) {
            $this->temp_html = preg_replace('/<body>/i', "<body><form><input id = '_posttoken' name = '_posttoken' type='hidden' value='$sign'>", $this->temp_html);
            $this->temp_html = preg_replace('/<\/body>/i', "</form></body>", $this->temp_html);
        } else {
            $this->temp_html = "<form><input id = '_posttoken' name = '_posttoken' type='hidden' value='$sign'>" . $this->temp_html . '</form>';
        }
        return $this;
    }

    //删除令牌
    public function delFormToken($sign) {
        unset($_SESSION['formtoken'][$sign]);
    }

    //锁令牌
    public function lockFormToken($sign) {
        $_SESSION['formtoken'][$sign] = 0;
    }

    //解锁令牌
    public function unlockFormToken($sign) {
        $_SESSION['formtoken'][$sign] = 1;
    }

    //得到模板文件的HTML内容
    public function getHtmlFromTemplateFile() {
        if (file_exists($this->templatefile)) {
            $fp = fopen($this->templatefile, 'r');
            $html = fread($fp, filesize($this->templatefile));
            fclose($fp);

            $this->temp_html = $html;
        } else {
            $this->temp_html = '警告：模版文件没找到或为空。';
        }
        return $this;
    }

    //组装模板页
    public function assemble($content) {
        if(!$content) {
            $myhtml = new myHTML();
            $this->temp_html =  $myhtml->getHTML($this->head . $this->temp_html. $this->foot, $this->temp_data);
		
            //完善<head>标签，加入SEO相关信息
            $this->temp_html = $myhtml->setHeadTag($this->temp_html, $this->head_title, $this->head_keywords, $this->head_description);
            //加入页面唯一标识符
            $this->setTempSign($this->temp_html);
            //加入提交令牌
			
            if ($this->useformtoken) {
                $this->setFormToken();
            }
            $this->temp_html = $this->temp_html;
        } else {
            $this->temp_html = $content;
        }
        $this->temp_html = preg_replace('/\{_TEMP_PUBLIC_\}/', _TEMP_PUBLIC_, $this->temp_html);
        $this->temp_html = preg_replace('/\{_TEMP_SHARE_\}/', _TEMP_SHARE_, $this->temp_html);
        $this->temp_html = preg_replace('/\{_TEMP_ACTION_\}/', _TEMP_ACTION_, $this->temp_html);
        $this->temp_html = preg_replace('/\{_TEMP_UPLOAD_\}/', _TEMP_UPLOAD_, $this->temp_html);
        $this->temp_html = preg_replace('/\{_TEMP_DOWNLOAD_\}/', _TEMP_DOWNLOAD_, $this->temp_html);
        $this->temp_html = preg_replace('/\{_TEMP_CACHE_\}/', _TEMP_CACHE_, $this->temp_html);
        if ($this->usecache && $this->temp_html) {
            $cache = new cache();
            $cache->set($this->sign, $this->temp_html, $this->cachetime);
        }
        return $this;
    }

    //刷新缓存页
    public function flush() {
        $cache = new cache();
        return $cache->del($this->sign);
    }

    //组装
    public function show($content = '') {
        echo $this->assemble($content)->temp_html;
    }

    public function run() {
        echo "Congratulate you, I'm working.";
    }

    // 局部模板过滤变量后输出html
    // $TempName 只接受相对路径的文件名（文件不要后缀)
    public function outputHTML($TempName = '', $TempData = array()) {
        $this->setTemplateFile($TempName);
        $this->setTemplateData($TempData);
        $this->getHtmlFromTemplateFile();
        $this->assemble(null);
        return $this->temp_html;
    }

    //得到HTML代码
    public function getHtml() {
        return $this->temp_html;
    }

    //js控件
    public static function jsWidget() {
        //使用nowdoc进行输出，变量中的字符串不会进行解析
        $jsCode = '';
        return $jsCode;
    }

    //校验权限
    /*
     * 重写了身份权限校验逻辑，根据校验结果，返回不同的代码，统一使用前端js进行处理。
     * 
     * 注意：考虑到平台架构的统一性和完整性，重写了前端架构，后端返回的处理结果，不再提倡
     * 使用message::show方式返回，而统一采用message::getJsonMsgStruct方式返回json
     * 结构化数据，达到前后端框架通讯格式的统一。
     * 
     */
    public function checkPower($act, $power) {
			
		$curUrl = F::GetCurUrl();
 		
        if (in_array(0, $act)) {			
            return true; //无身份都可以访问
        }
		
        $this->cuser = new user();
        switch ($this->cuser->testLoginState($act, $power)) {
            case 1:
                return true;
            case -1://未登录
				
				if(strpos($_SERVER['REQUEST_URI'], '.json') === false){//如果不是提交表单					
					//$fla = isset($this->options['_ajax']) ? '/?return=' : '';		
					//$this->protocal					
					if(isset($this->options['_ajax'])){//如果是通过ajax请求页面,那么用#拼接链接
						$fla = '/#/';
						$_SERVER['REQUEST_URI'] = str_replace('_ajax', 'ajax', $_SERVER['REQUEST_URI']);
					}else{
						$fla = '';
					}
					$_SESSION['backUrl'] = $this->protocal.$_SERVER['HTTP_HOST'].$fla.$_SERVER['REQUEST_URI'];//可以跳到原来的页面
					/* echo $_SESSION['backUrl'];
					die; */
				}
                if ($act[0] == 2){
					if(isset($this->options['_ajax'])){
						$this->show(message::getJsonMsgStruct('0004', array('text'=>'您还没有登录，请先登录。', 'url'=>$this->protocal.WORKERURL.'/login')));//没有登录						
					}else{
						header('location:'.$this->protocal	.WORKERURL.'/login');//没有登录的话，且需要登录雇员平台的话，直接跳到协作平台的登录界面
					}
                }else{
					if(isset($this->options['_ajax'])){
						$this->show(message::getJsonMsgStruct('0004', array('text'=>'您还没有登录，请先登录。', 'url'=>$this->protocal.UCURL.'/login')));//没有登录
					}
					else{
						if(F::isMobile()) {
							$url = $this->protocal.WAPURL."/login";
						}else{
							$url = $this->protocal.UCURL."/login";
						}
						header("location:{$url}");//没有登录的话，直接跳到用户中心的登录界面
					}	
                }
                exit;
            case -2://身份不对
                echo message::getMessageByID('0006');
                exit;
            case -3://没有权限
				
				if(isset($this->options['_ajax'])){
					$this->show(message::getJsonMsgStruct('0005'));					
				}else{					
					if(F::isMobile()) {
						$url = $this->protocal.WAPURL."/index/powerErr";
					}else{
						$url = $this->protocal.UCURL."/login/";
					}
					header('location:'.$url);
				}
               // echo message::getMessageByID('0005');
                exit;
			case -4://完善资料,激活账号
				header('location:'.$this->protocal	.UCURL.'/active');//
				//exit;
			case -5://登录密码
				header('location:'.$this->protocal	.WWWURL.'/wap/activateId1');
				//exit;
        }
    }















}
