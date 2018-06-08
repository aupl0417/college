<?php

class apilist extends guest {

    function __construct($options = array(), $power = array()) {
        parent::__construct($options, $power);
    }

    function run() {

		$this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');

        $db = new MySql();

        //定义两个变量储存html
        $html_tr = '';
        $html_th = '';

        //接口分类获取
        $sql = "select ic_id,ic_name from t_interface_category ORDER BY ic_order";
        $data = $db->getAll($sql);

        //手动添加API错误对照码
        $key = count($data);
        $this->setLoopData('category',$data);

        if(isset($this->options['id'])){
            if($this->options['id'] != 99){
                //分类接口信息
                $sql = "select * from t_interface_list where il_ic_id ='".$this->options['id']."' and il_reviewed = '1'";
                $listdata = $db->getAll($sql);
                if(!empty($listdata)){
                    foreach($listdata as $key=>$val){
                        $html_tr .= '<tr>
                                <td><a href="/index/detail/?c_id='.$val['il_ic_id'].'&id='.$val['il_id'].'">'.$val['il_interface_url'].'</a></td>
                                <td>'.$val['il_title'].'</td>
                             </tr>';
                    }
                }else{
                    $html_tr = '<td align="center" colspan="4" style="font-size: 24px;height: 150px">暂时没有接口数据！</td>';
                }
                $html_th = '<tr>
                                <th colspan="4">API列表</th>
                            </tr>';
                $param = array(
                        'html_th'   =>  $html_th,
                        'html_tr'   =>  $html_tr,
                    );
                $sql = "select ic_name from t_interface_category where ic_id='".$this->options['id']."'";
                $c_name = $db->getRow($sql);
            }
        }else{
            $sql = "select * from t_interface_list where il_ic_id = '1' and il_reviewed = '1'";
            $listdata = $db->getAll($sql);
            if(!empty($listdata)){
                foreach($listdata as $key=>$val){
                    $html_tr .= '<tr>
                                <td><a href="/index/detail/?c_id='.$val['il_ic_id'].'&id='.$val['il_id'].'">'.$val['il_interface_url'].'</a></td>
                                <td>'.$val['il_title'].'</td>
                             </tr>';
                }
            }else{
                $html_tr = '<td align="center" colspan="4" style="font-size: 24px;height: 150px">暂时没有接口数据！</td>';
            }
            $html_th = '<tr>
                            <th colspan="4">API列表</th>
                        </tr>';
            $param = array(
                'html_th'   =>  $html_th,
                'html_tr'   =>  $html_tr,
            );
            $c_name['ic_name'] = '用户';
        }
        //分类名称
        $data = array(
            'name' =>$c_name['ic_name'],
            'html_th'  =>$param['html_th'],
            'html_tr'  =>$param['html_tr'],
        );
        $this->setReplaceData($data);

        $this->setTempAndData('apilist/apilist');
        $this->show();
    }


}