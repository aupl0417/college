<?php

class getDownLoadList_json extends guest {
    
    function run() {
        //type 0：获取PPT 1：获取视频
        $type = isset($this->options['type']) && !F::isEmpty($this->options['type']) ? $this->options['type'] + 0 : 1;
        $page = isset($this->options['page']) && !empty($this->options['page']) ? $this->options['page'] + 0 : 1;
        $pageSize = 8;
        
        $db = new MySql();
        $sql = 'select cr_id as id,cr_name as name,crd_url as url from tang_course_resource left join tang_course_resource_file on crd_resourceId=cr_id where cr_type="' . $type . '" ';
        $order = 'order by cr_createTime desc limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
        if($type == 1){
            $sql .= ' and crd_url like "%.mp4%"';
        }else {
            $sql .= ' and crd_url like "%.ppt%"';
        }

        $list = $db->getAll($sql . $order);

        !$list && die($this->show(message::getJsonMsgStruct('1002', '暂无数据')));
        
        if($type == 0){
            $list = $this->dealFileList($list);
        }else if($type == 1){
            $list = $this->dealVideoList($list);
        }
        
        !count($list) && die($this->show(message::getJsonMsgStruct('1002', '暂无数据')));
        $this->show(message::getJsonMsgStruct('1001', $list));
        
    }
    
    //整理视频数据
    private function dealVideoList(&$videoList){
        if(!$videoList){
            return array();
        }
        
        foreach($videoList as $key=>&$val){
            $url = unserialize($val['url']);
            if(isset($url['videoUrl'])){
                $videoInfo = parse_url($url['videoUrl']);
                $path = trim($videoInfo['path'], '/');
//                 if(substr_count(strtolower($path), '.mp4') == 0 || substr_count(strtolower($path), 'mp4_') == 0){
                if(substr_count(strtolower($path), '.mp4') == 0){
                    unset($videoList[$key]);
                    continue;
                }
                
                $val['videoUrl']   = $url['videoUrl'];
                $val['videoImage'] = $url['videoImage'];
                unset($videoList[$key]['url']);
            }else{
                unset($videoList[$key]);
            }
        }
        
        $length = count($videoList);
        
        return array_chunk($videoList, $length)[0];
    }
    
    //整理文件数据
    private function dealFileList($fileList){
        if(!$fileList){
            return array();
        }
        
        foreach($fileList as $fkey=>$fval){

            $url = unserialize($fval['url']);
            foreach($url as $k=>$v){
                $fileUrlArr = explode('.', $v['fileUrl']);
                if(in_array(strtolower(end($fileUrlArr)), array('ppt', 'pptx'))){
                    $list[$fkey] = $v;
//                    $v['className'] = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $v['className']);
                    $list[$fkey]['filename'] = preg_replace('/(Ｃ|C)\s*(\+|＋)?/', 'C<sup>＋</sup>', $fval['name']);
                    $list[$fkey]['key']      = trim(parse_url($v['fileUrl'])['path'], '/');
                    $list[$fkey]['id']       = $fval['id'];
                }
            }
        }

        return $list;
    }

}
