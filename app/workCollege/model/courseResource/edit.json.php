<?php

class edit_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);			
    }
    //暂时不做
    function run() {
        if(F::isEmpty($this->options['type'])){
            $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
        }
        
        if(F::isEmpty($this->options['id'])){
            $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
        }
        $id   = $this->options['id'] + 0;
        $cid  = $this->options['cid'] + 0;
        $type = $this->options['type'] + 0;
        
        $name = $this->options['name'];
        if(empty($name)){
            $this->show(message::getJsonMsgStruct('1002',  '课件资源名称不能为空'));exit;
        }
        
        if(empty($this->options['courseId'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        $courseId = $this->options['courseId'] + 0;
        
        $type = $this->options['type'] + 0;
        $fileUrl = array();
        $db = new MySql();
        if($type == 0){//文档文件
            
            if($this->options['Files']){
                $up = new uploadFile($this->options['Files'], '', array('ppt'), 2000, 0, 1, 2);
                if (!$up->run('upload')){
                    $msg_err = $up->errmsg();
                    $this->show(message::getJsonMsgStruct('1002', '上传失败'.$msg_err)); //失败
                    exit;
                }
            
                //文件信息对象
                $fileObj = $up->getInfo();
                if($fileObj){
                    foreach($fileObj as $key=>&$val){
                        $fileUrl[$key]['filename'] = $val['name'];
                        $fileUrl[$key]['fileUrl'] = TFS_APIURL . '/' . $val['saveName'];
                    }
                }
            }
            
            $count = count($fileUrl);
            if(isset($this->options['filename']) && $this->options['filename'] && isset($this->options['fileUrl']) && $this->options['fileUrl']){
                foreach($this->options['filename'] as $key=>$val){
                    $index = $count;
                    foreach($this->options['fileUrl'] as $k=>$v){
                        if($key == $k){
                            $fileUrl[$index + $key]['filename'] = $val;
                            $fileUrl[$index + $key]['fileUrl']  = $v;
                        }
                    }
                }
            }
            $urlData = serialize($fileUrl);
        }elseif($type == 1){
            $fileUrl = $db->getField('select crd_url from tang_course_resource_file where crd_resourceId="' . $id . '"');
            $fileUrl = unserialize($fileUrl);
            
            if(isset($this->options['video']) && !empty($this->options['video'])){
                $videoArr = explode('.', $this->options['video']);
                if(strtolower(end($videoArr)) != 'mp4'){
                    die($this->show(message::getJsonMsgStruct('1002', '请输入mp4格式的视频')));
                }
            }
            
            if(isset($this->options['videoUrl']) && !empty($this->options['videoUrl'])){
                $fileUrl['videoUrl'] = $this->options['videoUrl'];
            }
            
            if(isset($this->options['logo']) && !empty($this->options['logo'])){
                $fileUrl['videoImage'] = TFS_APIURL . '/' . $this->options['logo'];
            }
            
            $urlData = serialize($fileUrl);
        }else{//网页文章
            if(isset($this->options['url'])){
                if(empty($this->options['url'])){
                    $this->show(message::getJsonMsgStruct('1002',  '请添加文件地址'));exit;
                }
                $url = $this->options['url'];
                
                $urlData = $this->fileUrlCheck($url);
                if(!$urlData){
                    $this->show(message::getJsonMsgStruct('1002',  '请添加文件或地址'));exit;
                }else if($urlData == 2){
                    $this->show(message::getJsonMsgStruct('1002',  '地址格式非法'));exit;
                }
            }
        }
        
        $time = time();
        $branchId = 0;//暂时为0,以后根据userId从用户表中获取
        $data = array(
            'cr_name'       => $name,
            'cr_courseId'   => $courseId,
            'cr_branchId'   => $branchId ? $branchId : 0, 
            'cr_isPublic'   => $this->options['isPublic'] + 0,
            'cr_updateTime' => date('Y-m-d H:i:s', $time),
        );
        
        try {
            $db->beginTRAN();
            $res = $db->update('tang_course_resource', $data, 'cr_id="' . $id .'"');
            
            if($res === false){
                throw new Exception('更新资源主表失败');
            }
            if($fileUrl){
                $courseResourceFile = array(
                    'crd_resourceId' => $id,
                    'crd_url'        => $urlData
                );
                
                $result = $db->update('tang_course_resource_file', $courseResourceFile, 'crd_id="' . $cid . '"');//都是地址形式，都统一保存到同一个附表
                if($result === false){
                    throw new Exception('更新附表失败');
                }
            }
            
            $db->commitTRAN();
            
            $this->show(message::getJsonMsgStruct('1001', '编辑成功'));exit;
            
        } catch (Exception $e) {
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002',  '编辑失败'));exit;
        }
    }
    
    private function fileUrlCheck($data){
        if(!is_array($data) || empty($data)){
            return false;
        }
        $reg = '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/';
        foreach($data as $key=>$val){
            if(empty($val)){
                unset($data[$key]);
            }else {
                if(preg_match($reg, $val) !== 1){
                    return 2;
                }
            }
        }
        
        return serialize($data);
    }
}
