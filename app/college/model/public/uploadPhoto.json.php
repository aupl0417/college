<?php

class uploadPhoto_json extends member {
    function run() {
        $opts = array( 'http'=>array(
            'method'=>"GET",
            'header'=>"User-Agent: Mozilla/5.0\n"
            )
        );

        $fileFormat = array('jpg', 'png', 'gif', 'zip','rar','doc','docx','xls','xlsx','pdf','txt');
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
