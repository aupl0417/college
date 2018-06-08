<?php

class add_json extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);			
    }
    
    function run() {
        $type = $this->options['type'];
        if(F::isEmpty($type)){
            $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
        }
        
        $name = $this->options['name'];
        if(empty($name)){
            $this->show(message::getJsonMsgStruct('1002',  '课件资源名称不能为空'));exit;
        }
        $courseId = $this->options['courseId'];
        if(empty($courseId)){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        
        $url = $this->options['url'];
        if(empty($this->options['url'])){
            $this->show(message::getJsonMsgStruct('1002',  '请选择所属课程'));exit;
        }
        
        $res = $this->fileUrlCheck($url);
        if(!$res){
            $this->show(message::getJsonMsgStruct('1002',  '请添加文件或地址'));exit;
        }else if($res == 2){
            $this->show(message::getJsonMsgStruct('1002',  '地址格式非法'));exit;
        }
        
        $time = time();
        $branchId = 0;//暂时为0,以后根据userId从用户表中获取
        $data = array(
            'cr_name'       => $name,
            'cr_courseId'   => $courseId + 0,
            'cr_userId'     => $_SESSION['userID'],
            'cr_type'       => $type + 0,
            'cr_branchId'   => $branchId ? $branchId : 0, 
            'cr_isPublic'   => $this->options['isPublic'] + 0,
            'cr_updateTime' => date('Y-m-d H:i:s', $time),
            'cr_createTime' => date('Y-m-d H:i:s', $time),
        );
        $db = new MySql();
        try {
            $db->beginTRAN();
            $id = $db->insert('tang_course_resource', $data);
            
            if(!$id){
                throw new Exception('插入资源主表失败');
            }
            $courseResourceFile = array(
                'crd_resourceId' => $db->getLastID(),
                'crd_url'        => $res
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
