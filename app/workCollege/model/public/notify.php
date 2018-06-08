<?php

class notify extends guest {
	
    function run() {
		
		$qiniu = new QiniuStorage();
		$auth = $qiniu::getAuth();
		
		//获取回调的body信息
		$callbackBody = file_get_contents('php://input');
		file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test.txt", $callbackBody, FILE_APPEND);
		//回调的contentType
		$contentType = 'application/x-www-form-urlencoded';

		//回调的签名信息，可以验证该回调是否来自七牛
		$authorization = $_SERVER['HTTP_AUTHORIZATION'];
		
		// $url = 'http://workcollege.dterptest.com/public/notify';
		$url = 'http://172.30.251.210/callback.php';

		$isQiniuCallback = $auth->verifyCallback($contentType, $authorization, $url, $callbackBody);
		
		if ($isQiniuCallback) {
			$resp = array('ret' => 'success');
		} else {
			$resp = array('ret' => 'failed');
		}
		$resp = array('ret' => 'success');
		echo json_encode($resp);
		
		// $data = file_get_contents("php://input");
		// file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test.txt", json_encode($data), FILE_APPEND);
		// file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/test1.txt", json_encode($this->options), FILE_APPEND);
    }
}
