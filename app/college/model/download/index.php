<?php

class index extends guest {
	
	private $db;
    
    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
        $this->db = new MySql();
    }
	
    function run() {
        $this->setHeadTag('title', '课件下载-唐人大学'.SEO_TITLE);
		// $this->setHeadTag('keywords', ''.SEO_KEYWORDS);
		// $this->setHeadTag('description', SEO_DESCRIPTION.'');

        $field = 'cr_id as id,cr_name as name,crd_url as url';
        $order = 'order by cr_createTime desc';
        $table = 'tang_course_resource left join tang_course_resource_file on crd_resourceId=cr_id';

        //视频列表
        $videoList = array();
        $vwhere = 'where cr_type=1 and crd_url like "%.mp4%"';
        $videoPage = new page($table, $field, 1, 8, 0, $vwhere, $order);
        $videoPage->getDivPage();
        $videoList = $videoPage->getPage();
        
        //文件列表
        $fileList  = array();
        $fwhere = 'where cr_type=0';
        $filePage = new page($table, $field, 1, 8, 0, $fwhere, $order, 'getFileList');
        $filePage->getDivPage();
        $fileList = $filePage->getPage();

        $videoList = $this->dealVideoList($videoList);
        $fileList  = $this->dealFileList($fileList);

        $this->setReplaceData(['menu' => $this->options['PATH_MODEL'], 'vpagelist' => $videoPage->getMenu(), 'fpagelist' => $filePage->getMenu()]);
        $this->setLoopData('videoList', $videoList);
        $this->setLoopData('fileList', $fileList);
		$this->setTempAndData();
        $this->show();
    }
    
    //整理视频数据
    private function dealVideoList(&$videoList){
        if(!$videoList){
            return array();
        }
        
        foreach($videoList as $key=>&$val){
            $url = unserialize($val['url']);
            if(isset($url['videoUrl'])){
                $val['videoUrl']   = $url['videoUrl'];
                $val['videoImage'] = $url['videoImage'];
                unset($videoList[$key]['url']);
            }else{
                unset($videoList[$key]);
            }
        }
        
        $length = count($videoList);
        
        return array_chunk($videoList, $length)[0];
    }
    
    //整理文件数据
    private function dealFileList($fileList){
        if(!$fileList){
            return array();
        }
        
        foreach($fileList as $fkey=>$fval){

            $url = unserialize($fval['url']);
            foreach($url as $k=>$v){
                $fileUrlArr = explode('.', $v['fileUrl']);
                if(in_array(strtolower(end($fileUrlArr)), array('ppt', 'pptx'))){
                    $list[$fkey] = $v;
//                    $v['className'] = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $v['className']);
                    $list[$fkey]['filename'] = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $fval['name']);
                    $list[$fkey]['key']      = trim(parse_url($v['fileUrl'])['path'], '/');
                    $list[$fkey]['id']       = $fval['id'];
                }
            }
        }

        return $list;
    }
}
