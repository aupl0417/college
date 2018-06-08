<?php

class addResource_json extends member {

    function __construct($options) {        		
        parent::__construct($options, [502]);			
    }
    
    function run() {
        
        if(F::isEmpty($this->options['type'])){
            $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
        }
        
        if(!isset($this->options['name']) || empty($this->options['name'])){
            $this->show(message::getJsonMsgStruct('1002',  '课件资源名称不能为空'));exit;
        }
        
        if(!isset($this->options['courseId']) || empty($this->options['courseId'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        
        $name = $this->options['name'];
        $courseId = $this->options['courseId'] + 0;
        $type = $this->options['type'] + 0;
        $this->qiniu = new QiniuStorage();

        if($type == 0){

            if(empty($this->options['filename'])){
                $this->show(message::getJsonMsgStruct('1002',  '请上传文档'));exit;
            }

            $filesArray = explode(',', trim($this->options['filename'], ','));
            $keysArray  = explode(',', trim($this->options['keys'], ','));
            $length = count($filesArray);

            for($i = 0; $i < $length; $i++){
                //获取文件后缀
                $tempArr = explode('.', $filesArray[$i]);
                $ext = end($tempArr);

                if(!in_array(strtolower($ext), array('ppt', 'pptx'))){
                    die($this->show(message::getJsonMsgStruct('1002', '文件格式必须是ppt或pptx')));
                }

                $this->qiniu->rename($keysArray[$i], $filesArray[$i]);
                $fileUrl[$i]['filename'] = $filesArray[$i];
                $fileUrl[$i]['fileUrl'] = 'http://' . $this->qiniu->DOMAIN . '/' . $filesArray[$i];
            }
        }else if($type == 1){
            if(!isset($this->options['logo']) || empty($this->options['logo'])){
                die($this->show(message::getJsonMsgStruct('1002',  '请上传视频封面')));
            }
            
            if(!isset($this->options['video']) || empty($this->options['video'])){
                die($this->show(message::getJsonMsgStruct('1002', '视频名称不能为空')));
            }
            
            $videoArr = explode('.', $this->options['video']);
            if(strtolower(end($videoArr)) != 'mp4'){
                die($this->show(message::getJsonMsgStruct('1002', '请输入mp4格式的视频')));
            }

			$this->qiniu->rename($this->options['filekey'], $this->options['video']);
            $fileUrl = ['videoUrl' => $this->options['videoUrl'], 'videoImage' => TFS_APIURL . '/' . $this->options['logo']];
        }

        $time = time();
        $branchId = 0;//暂时为0,以后根据userId从用户表中获取
        $db = new MySql();
        $data = array(
            'cr_name'       => $name,
            'cr_courseId'   => $courseId + 0,
            'cr_userId'     => $db->getField('select id from tang_ucenter_member where userId="' . $_SESSION['userID'] . '"'),
            'cr_type'       => $type + 0,
            'cr_branchId'   => $branchId ? $branchId : 0, 
            'cr_isPublic'   => $this->options['isPublic'] + 0,
            'cr_updateTime' => date('Y-m-d H:i:s', $time),
            'cr_createTime' => date('Y-m-d H:i:s', $time),
        );
        
        try {
            $db->beginTRAN();
            $id = $db->insert('tang_course_resource', $data);
            
            if(!$id){
                throw new Exception('插入资源主表失败');
            }
            $courseResourceFile = array(
                'crd_resourceId' => $db->getLastID(),
                'crd_url'        => serialize($fileUrl)
            );
            $result = $db->insert('tang_course_resource_file', $courseResourceFile);//都是地址形式，都统一保存到同一个附表
            if(!$result){
                throw new Exception('插入附表失败');
            }
            
            $db->commitTRAN();
            
            $this->show(message::getJsonMsgStruct('1001', '添加成功'));exit;
            
        } catch (Exception $e) {
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002',  '添加失败'));exit;
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
