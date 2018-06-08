<?php

class addFile_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        if(empty($this->options['name'])){
            $this->show(message::getJsonMsgStruct('1002',  '课件资源名称不能为空'));exit;
        }
        
        if(empty($this->options['courseId'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        
        if(empty($this->options['Files'])){
            $this->show(message::getJsonMsgStruct('1002',  '请上传文档'));exit;
        }
        
//         $up = new uploadFile($this->options['Files'], '', array('jpg', 'png', 'gif', 'jpeg','txt', 'doc','ppt','xlsx','rar','zip', 'docx'), 2000, 0, 1, 2);
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
        
        $time = time();
        $branchId = 0;//暂时为0,以后根据userId从用户表中获取
        $data = array(
            'cr_name'       => $this->options['name'],
            'cr_courseId'   => $this->options['courseId'] + 0,
            'cr_userId'     => $_SESSION['userID'],
            'cr_type'       => 0,
            'cr_branchId'   => $branchId ? $branchId : 0,
            'cr_isPublic'   => $this->options['isPublic'] + 0,
            'cr_updateTime' => date('Y-m-d H:i:s', $time),
            'cr_createTime' => date('Y-m-d H:i:s', $time),
        );
        
        $db = new MySql();
        try {
            $db->beginTRAN();
            
            $res = $db->insert('tang_course_resource', $data);
            if(!$res){
                throw new Exception('插入资源表失败', -1);
            }
            
            $info = array(
                'crd_resourceId' => $db->getLastID(),
                'crd_url' => serialize($fileUrl)
            );
            
            $result = $db->insert('tang_course_resource_file', $info);
            if(!$result){
                throw new Exception('插入资源文件表失败', -1);
            }
            
            $db->commitTRAN();
            die($this->show(message::getJsonMsgStruct('1001', '添加成功')));
        } catch (Exception $e) {
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct('1002', $e->getMessage())));
        }
        
    }
}
