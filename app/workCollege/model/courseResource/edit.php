<?php

class edit extends worker {

    function __construct($options) {        		
        parent::__construct($options, [50040201]);
    }
	
    function run() {
        
		if(F::isEmpty($this->options['id'])){
		    $this->show(message::getJsonMsgStruct('1002',  '非法参数'));exit;
		}
		
		$id   = $this->options['id'] + 0;
		$data = array(
		    'code'          => 40201,
			'tempId'		=> 'temp_'.F::getGID(),
		    'cr_id'         => $id
		);
		
        $sql = "select cr_name,cr_courseId,cr_type as type,cr_isPublic,co_id,co_name,crd_id,crd_url from tang_course_resource 
               LEFT JOIN tang_course on cr_courseId=co_id 
               LEFT JOIN tang_course_resource_file on cr_id=crd_resourceId 
               where cr_id='" . $id . "'";
        
        $db  = new MySql();
        $result = $db->getRow($sql);
        
        $courseList = $db->getAll('select co_id, co_name from tang_course where co_state=1');//暂时全部选择，以后根据所在分院及下属分院来获取
        $data['courseList'] = '';
        foreach ($courseList as $key=>$val) {
            if($val['co_id'] == $result['co_id']){
                $data['courseList'] .= "<option value='" . $val['co_id'] . "' selected='selected'>" . $val['co_name'] . "</option>";
            }else {
                $data['courseList'] .= "<option value='" . $val['co_id'] . "'>" . $val['co_name'] . "</option>";
            }
        }
        $result['isShare'] = $result['cr_isPublic'] == 1 ? 'checked="checked"' : '';
        $result['unShare'] = $result['cr_isPublic'] == 0 ? 'checked="checked"' : '';
        $template = '';
        switch(intval($result['type'])){
            case 0:
                $template = 'editFile';
                break;
            case 1:
                $qiniu = new QiniuStorage();
                $param = array();
                $token = $qiniu->UploadToken($qiniu::SECRETKEY, $qiniu::ACCESSKEY, $param);
                $data['token'] = $token;
                $data['domain'] = $qiniu->DOMAIN;
                $template = 'editVideo';
                break;
            case 2:
                $data['resourceMenu'] = '网页文章';
                $resourceButton = '<input name="url[]" type="text" value="%s" class="%s" style="%s" placeholder="请输入网页文章地址"><button type="button" class="btn btn-success button"><i class="fa"></i> 添加地址</button>';
                $data['addButton'] = '<input name="url[]" type="text" class="form-control col-md-8" style="width:50%;" placeholder="请输入网页文章地址"><button type="button" class="btn btn-success" onclick="delButton(this);"><i class="fa"></i> 删 除</button>';
                $addButton = '<input name="url[]" type="text" value="%s" class="%s" style="%s" placeholder="请输入网页文章地址"><button type="button" class="btn btn-success" onclick="delButton(this);"><i class="fa"></i> 删 除</button>';
                break;
        }
        
//         $urlArray = array();
        if(intval($result['type']) != 1){
            if(!empty($result['crd_url'])){
                $urlArray = unserialize($result['crd_url']);
                $data['urlListString'] = '';
                if(intval($result['type']) == 2 && $urlArray){
                    foreach ($urlArray as $key=>$val) {
                        if($key == 0){
                            $data['urlListString'] .= '<div class="row" style="margin-top:10px;margin-left:2px;">'. sprintf($resourceButton, $val, 'form-control col-md-8', 'width:50%;') . '</div>';
                        }else {
                            $data['urlListString'] .= '<div class="row" style="margin-top:10px;margin-left:2px;">'. sprintf($addButton, $val, 'form-control col-md-8', 'width:50%;') . '</div>';
                        }
                    }
                }else {
                    $this->setLoopData('fileUrl', $urlArray);
                }
            }else {
                $data['urlListString'] .= '<div class="row" style="margin-top:10px;margin-left:2px;">'. sprintf($resourceButton, '', 'form-control col-md-8', 'width:50%;') . '</div>';;
            }
        }else {
            $videoInfo = unserialize($result['crd_url']);
            $data = array_merge($data, $videoInfo);
        }
        
        $data = array_merge($data, $result);
        
		$this->setReplaceData($data);
        $this->setTempAndData($template);
        $this->show();
    }
}
