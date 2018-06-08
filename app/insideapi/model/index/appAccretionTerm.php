<?php

class appAccretionTerm extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {

//        $this->head = $this->head . F::readFile(APPROOT. '/template/cn/index/share/leftApi.html');
		$this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');

//        $this->setReplaceData('curPage', 2);
//        $page = !isset($this->options['id'])?1:$this->options['id'];
        $db = new MySql();

        //接口分类获取
        $sql = "select ic_id,ic_name from t_interface_category";
        $data = $db->getAll($sql);
        $this->setLoopData('category',$data);
        if(isset($this->options['id'])){
            //分类接口信息
            $sql = "select * from t_interface_list where il_ic_id ='".$this->options['id']."'";
            $listdata = $db->getAll($sql);
            foreach($listdata as $key=>$val){
                if($val['il_is_free'] == 1){
                    $listdata[$key]['il_is_free_text'] = '<td class="free">免费</td>';
                }else{
                    $listdata[$key]['il_is_free_text'] = '<td class="pay">收费</td>';
                }
            }
            $sql = "select ic_name from t_interface_category where ic_id='".$this->options['id']."'";
            $c_name = $db->getRow($sql);
        }else{
            $sql = "select * from t_interface_list where il_ic_id = '1'";
            $listdata = $db->getAll($sql);
            foreach($listdata as $key=>$val){
                if($val['il_is_free'] == 1){
                    $listdata[$key]['il_is_free_text'] = '<td class="free">免费</td>';
                }else{
                    $listdata[$key]['il_is_free_text'] = '<td class="pay">收费</td>';
                }
            }
            $c_name['ic_name'] = '用户';
        }
        $this->setLoopData('list',$listdata);

        //分类名称
        $data = array(
            'name' =>$c_name['ic_name'],
        );


        $this->setReplaceData($data);

//        $this->setReplaceData('id', $page);
        $this->setTempAndData('appAccretionTerm/appAccretionTerm');
        $this->show();
    }


}