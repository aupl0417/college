<?php

class glimpse extends worker {
    function __construct($options) {
        parent::__construct($options, [50010301]);
    }

    function run() {
        
        if(!isset($this->options['clID']) || empty($this->options['clID'])){
            die($this->show(message::getJsonMsgStruct('1002', '非法参数')));
        }
        
        $data = array(
            'code' => '50010301',
            'classId' => $this->options['clID'] + 0,
        );
        
        $db = new MySql();
        $imageList = $db->getAll('select tcp_id as id,tcp_filename as filename,tcp_sort as sort,tcp_isLogo,tcp_title as title from tang_class_picture where tcp_classId="' . $data['classId'] . '" order by tcp_isLogo desc, tcp_sort asc');
        
        if(!$imageList){
            $classLogo = $db->getField('select cl_logo from tang_class where cl_id="' . $data['classId'] . '"');
            $imageList[0] = array(
                'filename'   => $classLogo,
                'title'      => '',
                'tcp_isLogo' => 1,
                'sort'       => 0,
            );
            
            //如果学员风采没有记录，则把班级LOGO插入到学员风采表，并作为封面图
            $db->insert('tang_class_picture', ['tcp_filename'=>$classLogo, 'tcp_title' => '', 'tcp_classId'=>$data['classId'], 'tcp_sort'=>0, 'tcp_isLogo' => 1]);
        }
        
        foreach ($imageList as &$val){
            $val['filename'] = TFS_APIURL . '/' . $val['filename'];
            $val['title']    = $val['title'] ? (mb_strlen($val['title'], 'utf-8') > 30 ? mb_substr($val['title'], 0, 30, 'utf-8') . '...' : $val['title']) : '　';
            $val['class']    = $val['tcp_isLogo'] == 1 ? 'logo-show' : '';
        }
        
        $this->setLoopData('imageList', $imageList);
        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
