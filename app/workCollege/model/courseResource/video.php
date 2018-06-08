<?php

class video extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);
    }
	
    function run() {
		
		
		$this->qiniu = new QiniuStorage();
// 		$fops = "avthumb/mp4/s/640x360/vb/1.4m";
// 		$fops = "avthumb/mp4/s/1280x720/vb/5m";
		//要进行视频截图操作
		$fops = "vframe/jpg/offset/13/w/1280/h/720/rotate/auto";
		$key  = 'mp4_21.mp4';
		$res  = $this->qiniu->execute($key, $fops);
		$status = $this->qiniu->status($res[0]);
        list($ret, $err) = $this->qiniu->status($res[0]);
        
        
//         while(1){
            if(array_key_exists('key', $ret['items'][0])){
                dump($ret['items'][0]['key']);
                $result = $this->qiniu->rename($ret['items'][0]['key'], 'mp4_21.jpg');
                dump($result);
                break;
            }
//         }
		
    }
}
