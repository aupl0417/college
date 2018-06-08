<?php
/*=============================================================================
#     FileName: uploadPhoto.json.php
#         Desc: 图片上传
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-11-11 16:44:42
#      History:
#      Paramer:
=============================================================================*/
class uploadPhoto_json extends guest {
    function run() {
        $type = isset($this->options['type']) && !empty($this->options['type']) ? $this->options['type'] : 'all';
        $opts = array( 'http'=>array(
            'method'=>"GET",
            'header'=>"User-Agent: Mozilla/5.0\n"
            )
        );
        
        if($type == 'image'){
            $fileFormat = array('jpg', 'png', 'gif', 'jpeg');
        }else {
            $fileFormat = array('jpg', 'png', 'gif', 'zip','rar','doc','docx','xls','xlsx','pdf','txt');
        }
        
        $up = new uploadFile($this->options['Files'],'',$fileFormat,2000,0,1,2);

        if (!($up->run('file'))){
            die(json_encode(array('status'=>'error', 'message'=>  $up->getInfo()[0]['error'])));
        }

        $saveName = $up->getInfo()[0]['saveName'];
        $oldName = $up->getInfo()[0]['name'];
        $imgUrl = TFS_APIURL.'/'.$up->getInfo()[0]['saveName'];
        /*获取文件的后缀和文件名*/
        $suffix = pathinfo($imgUrl,PATHINFO_EXTENSION);
        echo json_encode(array('status'=>'success', 'filename'=>$imgUrl,'savename'=>$saveName,'suffix'=>$suffix,'name'=>$oldName));
    }
}
