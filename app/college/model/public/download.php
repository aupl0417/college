<?php

class download extends guest {

    function run() {
        //type:1 证书/文档 2 课件 3：ppt
        $type = isset($this->options['type']) && !empty($this->options['type']) ? $this->options['type'] + 0 : 1;

        $db = new MySql();
        if($type == 2) {//下载课件（图片和H264编码的MP4视频会直接在浏览器中打开，其它的则会下载）
            (!isset($this->options['id']) || empty($this->options['id'])) && die($this->show(message::getJsonMsgStruct('1002', '参数非法')));
            $id  = $this->options['id'] + 0;
            $url = $db->getField('select crd_url from tang_course_resource_file where crd_resourceId="' . $id . '"');
            header("Location:" . unserialize($url)['videoUrl']);
        }else {//下载证书/文档
            if(isset($this->options['key']) && !empty($this->options['key'])){
                $file_name = $this->options['key'];
            }else if(isset($this->options['id']) && !empty($this->options['id'])){
                $id  = $this->options['id'] + 0;
                $file_name = $db->getField('select tce_url from tang_certificate where tce_id="' . $id . '"');
            }

            if($type == 3){
                $qiniu = new QiniuStorage();
                $file_dir = 'http://' . $qiniu->DOMAIN . "/";
                $readCount = $db->getField('select cr_readCount from tang_course_resource where cr_id="' . intval($this->options['file_id']) . '"');
                $db->update('tang_course_resource', ['cr_readCount' => $readCount + 1], 'cr_id="' . intval($this->options['file_id']) . '"');
            }else {
                $file_dir = TFS_APIURL . "/";
            }

            $file = @fopen($file_dir . $file_name, "r");
            if (!$file) {
                die("File Not Found.");
            }
            
            header("content-type: application/octet-stream");
            header("content-disposition: attachment; filename=" . $file_name);
            while (!feof ($file)) {
                echo fgetc($file);
            }
            fclose ($file);
        }
        
    }
    
}
