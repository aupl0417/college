<?php

class review extends worker {
    function __construct($options) {
        parent::__construct($options, [50010503]);
    }

    function run() {
        $data = array(
            'code'   => '50010503',
            'tempId' => 'temp_' . F::getGID()
        );

        $options = $this->options;
        if (!isset($options['id']) || empty($options['id'])) {
            die($this->show(message::getJsonMsgStruct(1002, '参数错误')));
        }
        
        $db = new MySql();

        $field = 'tse_id,tse_userTrueName as trueName,username,tse_classId as classId,tse_fee as fee,tse_state,tse_status,tse_createTime,tse_payTime as payTime,tse_payFee as payFee,cl_name as className,cl_state';
        $sql = "SELECT {$field} FROM tang_student_enroll LEFT JOIN tang_class ON cl_id=tse_classId LEFT JOIN tang_ucenter_member on tse_userId=id 
                where tse_id='" . $options['id'] . "'";

        $info = $db->getRow($sql);
        if (empty($info)) {
            die($this->show(message::getJsonMsgStruct(1002, '获取信息失败')));
        }
        
        if(in_array($info['tse_status'], array(1, 2)) && $info['payTime'] == null){
            $info['payTime'] = $info['tse_createTime'];
        }
        $statusList = array(-1=>'关闭','未付款','已付款', '已使用');
        $info['status'] = $statusList[$info['tse_status']];
        

        $data = array_merge($data, $info);

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}
