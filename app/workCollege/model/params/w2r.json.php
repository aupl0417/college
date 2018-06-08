<?php

class w2r_json extends worker {

    function __construct($options) {
        parent::__construct($options, []);
    }

    function run() {

        $redis = new myRedis();
        if ($redis->get('job.w2r.info')) {
            $this->show(message::getJsonMsgStruct('5008'));
            exit;
        }

        $db = new MySql();
        $sql = 'SELECT count(*) as num FROM t_whitetored_return WHERE wrr_isProgress = 0';
        $num = $db->getField($sql);
        if ($num == 0) {
            $this->show(message::getJsonMsgStruct('5007'));
            exit;
        }
        system("php frame/job.php XCVBNMOUY456 2 >> frame/log/doW2R" . date('Y-m-d') . ".log &");
        $redis->set('job.w2r.info', 1);
        $this->show(message::getJsonMsgStruct('5008'), $num);
    }

}
