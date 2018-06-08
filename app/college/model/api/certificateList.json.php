<?php
/**
 * @api                    {post} /api/certificateList.json 证书列表
 * @apiDescription         证书列表
 * @apiName                certificateList_json
 * @apiGroup               api
 * @apiPermission          aupl
 *
 * 
   
   @apiParam {int}        userId     用户ID
   @apiParam {int}        userType   用户类型 
 *
 *
 * @apiSuccessExample      Success-Response:
 *{
    code: 1001,
    msg: "操作成功",
    data: {
        count: 1,
        certList: [
            {
                id: "1",  //证书编号
                name: "初级班毕业证书",    //证书名称
                createTime: "2016-11-23 17:38:49",   //获得时间
                url: "http://192.168.3.201:80/v1/tfs/T11yETBCLT1RCvBVdK.jpg"   //证书地址
            }
        ]
    }
  }
 *
 * @apiErrorExample        Error-Response:
 *{
    code: '1002',
    data: '用户ID不能为空'
  }
 *
 */
class certificateList_json extends api {

    function run() {
        if(!isset($this->options['userId'])  || empty($this->options['userId']))  return apis::apiCallback('1002', '用户ID不能为空');
        
        $userId   = $this->options['userId'] + 0;
        $userType = isset($this->options['userType']) ? $this->options['userType'] + 0 : 1;
        
        $page     = isset($this->options['page']) ? $this->options['page'] + 0 : 1;
        $pageSize = isset($this->options['pageSize']) ? $this->options['pageSize'] + 0 : 10;
        $pageSize == 0 || $page == 0 && apis::apiCallback('1002', '分页参数非法');
        
        $db = new MySql();
        $sql = 'select tce_id as id,tce_name as name,tce_createTime as createTime,tce_url as url from tang_certificate 
            where tce_userId="' . $userId . '" order by tce_createTime desc limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        $list = $db->getAll($sql);
        
        if(!$list){
            $data = array(
                'count'    => 0,
                'certList' => array()
            );
            return apis::apiCallback('1001', $data);
        }
        
        foreach($list as &$val){
            $val['url'] = $val['url'] ? TFS_APIURL . '/' . $val['url'] : '';
        }
        
        $data = array(
            'count'    => count($list),
            'certList' => $list
        );
        
        return apis::apiCallback('1001', $data);
    }
}
