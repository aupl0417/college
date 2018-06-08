<?php

class calc_json extends worker {

    function __construct($options) {
        parent::__construct($options, []);
    }

    function run() {
        $date = isset($this->options['date']) ? $this->options['date'] : '';
        $inper = isset($this->options['inper']) ? $this->options['inper'] : 0.1;
        $outper = isset($this->options['outper']) ? $this->options['outper'] : 0.01;

        if ($date == '') {
            $this->show(message::getJsonMsgStruct('5006'));
            exit;
        }
//        if (!score::getTodayStatistics($date)){
//            $this->show(message::getJsonMsgStruct('5006'));
//        }
        if (!score::updateTodayStatistics($date, $inper, $outper)) {
            if (score::$error == -6) {
                $this->show(message::getJsonMsgStruct('5009'));
            } else {
                $this->show(message::getJsonMsgStruct('5006'));
            }
        } else {
            $db = new MySql();
            $sql = 'SELECT ss_date,ss_wrTodayPoolInPer,ss_wrTodayPoolOutPer,ss_wrTodayNewWhiteScore,ss_wrTodayPoolScore,ss_wrReturnUnionTotal,ss_wrUnionScore,ss_wrReturnScore,ss_wrNotReturnScore,ss_wrTotalReturnScore,ss_wrTotalUseRedScore from t_statistics_system order by ss_id desc limit 1';
            $ret = $db->getRow($sql);
            $info = '';
            foreach ($ret as $v) {
                $info .= sprintf('<td>%s</td>', $v);
            }
            $this->show(message::getJsonMsgStruct('5005', $info));
        }
    }

}
