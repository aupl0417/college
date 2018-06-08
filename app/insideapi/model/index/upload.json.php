<?php
/*
 * @access              open 图片上传
 * ---------------------------------------------------------------------------------------------------------------------
 * @author              Dolen Chan
 * ---------------------------------------------------------------------------------------------------------------------
 * @copyright           ydw                                 广州一道网信息科技有限公司
 * ---------------------------------------------------------------------------------------------------------------------
 * @version             1.0.0
 * ---------------------------------------------------------------------------------------------------------------------
 * @time                2016-01-03
 * ---------------------------------------------------------------------------------------------------------------------
 */
class upload_json extends guest
{
    function __construct($options)
    {
        parent::__construct($options, []);
    }

    function run()
    {
		$up = new uploadFile($this->options['Files'],'','',2400,0,1,2);
		if ($up->run('file')){
		/*	$this->show(message::getJsonMsgStruct('1001',TFS_APIURL.'/'.$up->getInfo()[0]['saveName']));
		}else{
			$this->show(message::getJsonMsgStruct('1002'));
		}*/
			echo json_encode(array('status'=>'success', 'filename'=>TFS_APIURL.'/'.$up->getInfo()[0]['saveName']));
		}else{
			echo json_encode(array('status'=>'error', 'message'=>  $up->getInfo()[0]['error']));
		}
    }

}
