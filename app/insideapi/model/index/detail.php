<?php

class detail extends guest {

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run() {
//        $this->head = $this->head . F::readFile(APPROOT. '/template/cn/api/share/leftApiDetail.html');
        $this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');

        $db = new MySql();
        if(!isset($this->options['c_id'])){
            header('location:http://'.INSIDEAPI.'/index/apilist');
        }
        //获取该分类下的所有接口
        $sql = "select il_title,il_name,il_id,il_ic_id,il_interface_url,il_description,il_example from t_interface_list where il_ic_id='".$this->options['c_id']."' and il_reviewed = '1'";
        $c_data = $db->getAll($sql);
        $this->setLoopData('category',$c_data);
        //接口信息
        $api = array();
        foreach($c_data as $key=>$val){
            if($val['il_id'] == $this->options['id']){
                $api['name'] = $val['il_name'];
                $api['title'] = $val['il_title'];
                $api['url'] = $val['il_interface_url'];
                $api['description'] = $val['il_description'];
                $api['il_example'] = $val['il_example'];
            }
        }
        $this->setReplaceData($api);

        //获取当前接口请求的参数
        $sql = "select * from t_interface_request_field where iqf_il_id='".$this->options['id']."'";
        $request = $db->getAll($sql);
        $request_public = array();
        $request_self = array();
        foreach($request as $key=>$val){
            if($val['iqf_il_is_public'] == 1){
                $request_public[] = $val;
                foreach($request_public as $k=>$v){
                    if($v['iqf_il_required'] == '1'){
                        $request_public[$k]['iqf_il_required_text'] = '是';
                    }else{
                        $request_public[$k]['iqf_il_required_text'] = '否';
                    }
                }
            }else{
                $request_self[] = $val;
                foreach($request_self as $k=>$v){
                    if($v['iqf_il_required'] == '1'){
                        $request_self[$k]['iqf_il_required_text'] = '是';
                    }else{
                        $request_self[$k]['iqf_il_required_text'] = '否';
                    }
                }
            }
        }
        $this->setLoopData('request_public',$request_public);
        $this->setLoopData('request_self',$request_self);
        $this->setReplaceData($request_self);
        $this->setReplaceData($request_public);

        //获取当前接口返回的参数
        $sql = "select * from t_interface_response_field where irf_il_id='".$this->options['id']."'";
        $response = $db->getAll($sql);
        $this->setLoopData('response',$response);
        $this->setReplaceData($response);

        $this->setTempAndData('detail/detail');
        $this->show();
    }

}