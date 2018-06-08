<?php

class addClassPicture_json extends worker {
    function __construct($options) {
        parent::__construct($options, [500103]);
    }

    function run() {
        if(!isset($this->options['classId']) || empty($this->options['classId'])){
            die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        }
        
        if(!$this->options['filename']){
            die($this->show(message::getJsonMsgStruct('1002', '请上传图片')));
        }
        
        $db = new MySql();
        $classId = $this->options['classId'] + 0;
        try {
            $db->beginTRAN();
            $count = $db->getField('select count(tcp_id) from tang_class_picture where tcp_classId="' . $classId . '"');
           
            $length = count($this->options['filename']);
            $data = array();
            for($i = 0; $i < $length; $i++){
                if(mb_strlen($this->options['title'][$i],'utf-8') > 30){
                    die($this->show(message::getJsonMsgStruct('1002', "第" . ($i + 1) . "张图片的描述请控制在30个字符以内")));
                }
                $data[$i]['tcp_isLogo']   = 0; 
                $data[$i]['tcp_classId']  = $classId;
                $data[$i]['tcp_filename'] = $this->options['filename'][$i];
                $data[$i]['tcp_title']    = $this->options['title'][$i];
                $data[$i]['tcp_sort']     = $count + $i;
            }
            
            $res = $db->inserts('tang_class_picture', array('tcp_isLogo', 'tcp_classId', 'tcp_filename', 'tcp_title', 'tcp_sort'), $data);
            
            if(!$res){
                throw new Exception('插入学员风采表失败', -1);
            }
            
//             if(!$count){
//                 $result = $db->update('tang_class', ['cl_logo'  => $data[0]['tcp_filename']], 'cl_id="' . $classId . '"');
//                 if($result === false){
//                     throw new Exception('更新班级LOGO失败', -2);
//                 }
//             }
            
            $db->commitTRAN();
            die($this->show(message::getJsonMsgStruct('1001', '添加成功')));
        } catch (Exception $e) {
            $db->rollBackTRAN();
            die($this->show(message::getJsonMsgStruct('1002', $e->getMessage())));
        }
        
    }
}
