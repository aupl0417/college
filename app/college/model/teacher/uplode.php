<?php

class uplode extends member {

    function __construct($options) {        		
        parent::__construct($options, [502]);
        $this->maxUplodeSize = ini_get('upload_max_filesize');//最大上传大小
        ini_set('max_execution_time', '600');//设置最大执行时间
        ini_set('memory_limit', ini_get('post_max_size') * 1.5 . 'M');
        $this->qiniu = new QiniuStorage();
    }
	
    function run() {
        $file = $this->options['Files']['qiniu_file'];
        //视频格式限制
        $ext = explode('.', $file['name'])[1];
        if(strtolower($ext) != 'mp4'){
            die($this->show(message::getJsonMsgStruct('1002', '视频必须为MP4格式')));
        }
        
        //视频大小限制
        if($file['size'] >= $this->maxUplodeSize * 1024 * 1024){
            die($this->show(message::getJsonMsgStruct('1002', '请上传小于' . $this->maxUplodeSize . '的视频')));
        }
        
        $fileBody = file_get_contents($file['tmp_name']);
        
        $fileInfo = array(
            'name'     => 'file',
            'fileName' => $file['name'],
            'fileBody' => $fileBody
        );
        
        $config = array();
        $result = $this->qiniu->upload($config, $fileInfo);
//         dump($result);die;
//         if(!$result){
//             die($this->show(message::getJsonMsgStruct('1002', '上传失败')));
//         }
        
        $this->show(message::getJsonMsgStruct('1001', $file['name']));
        
//         $key = $file['name'];
//         $fops = "vframe/jpg/offset/1/w/1280/h/720/rotate/auto";
//         $res  = $this->qiniu->execute($key, $fops);
    }
}
