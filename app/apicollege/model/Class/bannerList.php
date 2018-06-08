<?php
 /**
 * @api                    {post} /Class/bannerList 广告图列表
 * @apiDescription         首页广告图列表
 * @apiName                bannerList
 * @apiGroup               Class
 * @apiPermission          aupl
 *
 * @apiParam {int}        appId     设备类型
   @apiParam {string}     deviceID  设备id
   @apiParam {string}     signValue 签名串
   @apiParam {int}        width     图片宽度
 *
 *
 * @apiSuccessExample      Success-Response: 
 *	{
        code: 1001,
        msg: "操作成功",
        data: [
            "https://image.dttx.com/v1/tfs/T1dUYTB4DT1RCvBVdK.png"
        ]
    }
 *
 */
class bannerList extends newBaseApi{
	
    function __construct($options) {
        parent::__construct($options);
    }
	
    function run() {
        
        (!isset($this->data['width']) || empty($this->data['width'])) && $this->apiReturn(1003, '图片宽度不能为空');
        
        $width = $this->data['width'] + 0;
        if(!in_array($width, array(320, 375, 414, 480, 640, 768, 834, 960, 1024, 1536, 2048))){
            $this->apiReturn(1004);
        }
        
        $bannerList = array(
            '320'  => array('https://image.dttx.com/v1/tfs/T1pmYTBXWT1RCvBVdK.png'),
            '375'  => array('https://image.dttx.com/v1/tfs/T1GcYTBCJT1RCvBVdK.png'),
            '414'  => array('https://image.dttx.com/v1/tfs/T14UDTB5dT1RCvBVdK.png'),
            '480'  => array('https://image.dttx.com/v1/tfs/T1acYTBXYT1RCvBVdK.png'),
            '640'  => array('https://image.dttx.com/v1/tfs/T1YcYTBCJT1RCvBVdK.png'),
            '768'  => array('https://image.dttx.com/v1/tfs/T1X6DTB5xT1RCvBVdK.png'),
            '834'  => array('https://image.dttx.com/v1/tfs/T1JUYTBXYT1RCvBVdK.png'),
            '960'  => array('https://image.dttx.com/v1/tfs/T1BmYTBCET1RCvBVdK.png'),
            '1024' => array('https://image.dttx.com/v1/tfs/T1ymDTB5xT1RCvBVdK.png'),
            '1536' => array('https://image.dttx.com/v1/tfs/T1JcYTB4ET1RCvBVdK.png'),
            '2048' => array('https://image.dttx.com/v1/tfs/T1rUYTBCET1RCvBVdK.png'),
        );
        
        $this->apiReturn(1001, '', $bannerList[$width]);
    }
    
}
