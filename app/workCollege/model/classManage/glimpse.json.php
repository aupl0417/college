<?php

class glimpse_json extends worker {
    function __construct($options) {
        parent::__construct($options, [50010301]);
    }

    function run() {
        
        if(!isset($this->options['id']) || empty($this->options['id'])){
            die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        }
        
        if(!isset($this->options['act']) || F::isEmpty($this->options['act'])){
            die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        }
        
        $act       = $this->options['act'] + 0;
        $pictureId = $this->options['id'] + 0;
        $db = new MySql();
        
        $pictureInfo = $db->getRow('select tcp_id,tcp_isLogo,tcp_classId,tcp_sort,tcp_filename from tang_class_picture where tcp_id="' . $pictureId . '"');
        !$pictureInfo && die($this->show(message::getJsonMsgStruct('1002', '图片不存在')));
        try {
            $db->beginTRAN();
            switch($act){
                case 0:
                    $title = isset($this->options['title']) && !empty($this->options['title']) ? $this->options['title'] : '';
                    $res = $db->update('tang_class_picture', ['tcp_title' => $title], 'tcp_id="' . $pictureId . '"');
                    if($res === false){
                        throw new Exception('编辑失败');
                    }
                    
                    break;
                case 1://删除
                    $res = $db->delete('tang_class_picture', 'tcp_id="' . $pictureId . '"');//删除图片
                    if(!$res){
                        throw new Exception('删除LOGO失败');
                    }
                    
                    if($pictureInfo['tcp_isLogo']){//设置下一记录的图片为封面
                        $nextPic = $db->getRow('select tcp_id,tcp_filename from tang_class_picture where tcp_classId="' . $pictureInfo['tcp_classId'] . '" and tcp_isLogo!=1 order by tcp_isLogo desc,tcp_sort asc limit 1');
                        if($nextPic){
                            $result  = $db->update('tang_class_picture', ['tcp_isLogo' => 1], 'tcp_id="' . $nextPic['tcp_id'] . '"');
                            $upClass = $db->update('tang_class', ['cl_logo' => $nextPic['tcp_filename']], 'cl_id="' . $nextPic['tcp_id'] . '"');
                            if($result === false || $upClass === false){
                                throw new Exception('更新LOGO失败');
                            }
                        }
                    }
                    
                    break;
                case 2://上移
                    $prePic = $db->getRow('select tcp_id,tcp_sort from tang_class_picture where tcp_classId="' . $pictureInfo['tcp_classId'] . '" and tcp_sort<"' . $pictureInfo['tcp_sort'] . '" order by tcp_sort desc limit 1');
                    if(!$prePic){
                        die($this->show(message::getJsonMsgStruct('1001', '操作成功')));//没有上一张图，直接返回操作成功
                    }
                    $preSort= $prePic['tcp_sort'];
                    $sort   = $prePic ? $pictureInfo['tcp_sort'] : $preSort;
                    
                    $res = $db->update('tang_class_picture', ['tcp_sort' => $preSort], 'tcp_id="' . $pictureId . '"');
                    $result = $db->update('tang_class_picture', ['tcp_sort' => $sort], 'tcp_id="' . $prePic['tcp_id'] . '"');
                    if($res === false || $result === false){
                        throw new Exception('操作失败');
                    }
                    
                    break;
                case 3://下移
                    $nexPic = $db->getRow('select tcp_id,tcp_sort from tang_class_picture where tcp_classId="' . $pictureInfo['tcp_classId'] . '" and tcp_sort>"' . $pictureInfo['tcp_sort'] . '" order by tcp_sort asc limit 1');
                    if(!$nexPic){
                        die($this->show(message::getJsonMsgStruct('1001', '操作成功')));//没有下一张图，直接返回操作成功
                    }
                    
                    $res = $db->update('tang_class_picture', ['tcp_sort' => $nexPic['tcp_sort']], 'tcp_id="' . $pictureId . '"');
                    $result = $db->update('tang_class_picture', ['tcp_sort' => $pictureInfo['tcp_sort']], 'tcp_id="' . $nexPic['tcp_id'] . '"');
                    if($res === false || $result === false){
                        throw new Exception('操作失败');
                    }
                    
                    break;
                case 4://设置为封面
                    $res = $db->update('tang_class_picture', ['tcp_isLogo' => 0], 'tcp_classId="' . $pictureInfo['tcp_classId'] . '" and tcp_isLogo=1');
                    $result = $db->update('tang_class_picture', ['tcp_isLogo' => 1], 'tcp_classId="' . $pictureInfo['tcp_classId'] . '" and tcp_id="' . $pictureId . '"');
                    if($res === false || $result === false){
                        throw new Exception('设置失败');
                    }
                    
                    $upClassLogo = $db->update('tang_class', ['cl_logo' => $pictureInfo['tcp_filename']], 'cl_id="' . $pictureInfo['tcp_classId'] . '"');
                    if($upClassLogo === false){
                        throw new Exception('更新班级LOGO失败');
                    }
            }
            
            $db->commitTRAN();
            $this->show(message::getJsonMsgStruct('1001', '操作成功'));
            
        } catch (Exception $e) {
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002', '删除失败'));
        }
    }
}
