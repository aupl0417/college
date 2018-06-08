<?php

class addVideo_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);
    }
	
    function run() {
		
        if(!isset($this->options['name']) || empty($this->options['name'])){
            die($this->show(message::getJsonMsgStruct('1002', '课件名称不能为空')));
        }
        
        if(!isset($this->options['courseId']) || empty($this->options['courseId'])){
            die($this->show(message::getJsonMsgStruct('1002', '请选择所属课程')));
        }
        
        if(!isset($this->options['logo']) || empty($this->options['logo'])){
            die($this->show(message::getJsonMsgStruct('1002', '请上传视频图片')));
        }
        
        if(!isset($this->options['video']) || empty($this->options['video'])){
            die($this->show(message::getJsonMsgStruct('1002', '视频名称不能为空')));
        }
        
//         if(strtolower(explode('_', $this->options['video'])[0]) != 'mp4'){
//             die($this->show(message::getJsonMsgStruct('1002', '上传视频的视频名称格式不正确')));
//         }
        
        $videoArr = explode('.', $this->options['video']);
        if(strtolower(end($videoArr)) != 'mp4'){
            die($this->show(message::getJsonMsgStruct('1002', '请输入mp4格式的视频')));
        }
        
//         $qiniu = new QiniuStorage();
//         $info = $qiniu->info($this->options['video']);
        
//         if(!$info){
//             die($this->show(message::getJsonMsgStruct('1002', '请稍后重试！')));
//         }
        
        $db = new MySql();
        
        try {
            
            $db->beginTRAN();
            
            $time = date('Y-m-d H:i:s');
            
            $data = array(
                'cr_name' => $this->options['name'],
                'cr_courseId' => $this->options['courseId'] + 0,
                'cr_userId' => $_SESSION['userID'],
                'cr_type'  => 1,
                'cr_isPublic' => $this->options['isPublic'] + 0,
                'cr_updateTime' => $time,
                'cr_createTime' => $time
            );
            
            $res = $db->insert('tang_course_resource', $data);
            
            if(!$res){
                throw new Exception('加入资源表失败', -1);
            }
            
            $info = array(
                'crd_resourceId' => $db->getLastID(),
                'crd_url' => serialize(['videoUrl' => $this->options['videoUrl'], 'videoImage' => TFS_APIURL . '/' . $this->options['logo']])
            );
            
            $result = $db->insert('tang_course_resource_file', $info);
            if(!$result){
                throw new Exception('插入资源文件表失败', -2);
            }
            
            $db->commitTRAN();
            
            $this->show(message::getJsonMsgStruct('1001', '添加成功'));
            
        } catch (Exception $e) {
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002', $e->getMessage()));
        }
    }
}
