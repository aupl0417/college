<?php

class addVideo extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);
    }
	
    function run() {
		
		$data = array(
		    'code'          => 50040201,
			'tempId'		=> 'temp_'.F::getGID(),
		);
		
		$sql = "select co_id, co_name from tang_course where co_state=1";//暂时全部选择，以后根据所在分院及下属分院来获取
		$db  = new MySql();
		$courseList = $db->getAll($sql);
		
		$qiniu = new QiniuStorage();
		$data['domain'] = $qiniu->DOMAIN;
		$param = array();
		$token = $qiniu->UploadToken($qiniu::SECRETKEY, $qiniu::ACCESSKEY, $param);
		$data['token'] = $token;
// 		$this->qiniu = new QiniuStorage();
// // 		$fops = "avthumb/mp4/s/640x360/vb/1.4m";
// // 		$fops = "avthumb/mp4/s/1280x720/vb/5m";
// 		//要进行视频截图操作
// 		$fops = "vframe/jpg/offset/1/w/1280/h/720/rotate/auto";
// 		$key  = '19.mkv';
// 		$res  = $this->qiniu->execute($key, $fops);
// 		$status = $this->qiniu->status($res[0]);
//         list($ret, $err) = $this->qiniu->status($res[0]);
//         dump($err);
//         dump($ret);
		$this->setLoopData('courseList', $courseList);
		$this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
