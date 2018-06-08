<?php

class w2rReport_json extends worker {

    function __construct($options) {
        parent::__construct($options, []);
    }

    function run() {

        $redis = new myRedis();
        if (!$redis->get('job.w2r.info')) {
            $this->show(message::getJsonMsgStruct('5007'),0);
            exit;
        }

        $db = new MySql();
        $sql = 'SELECT count(*) as num FROM t_whitetored_return WHERE wrr_isProgress = 1';
        $num = $db->getField($sql);
        if ($num == 0) {
            $redis->set('job.w2r.info',0);
            $sql = 'update t_statistics_system set SET ss_isPublish = 1';
            $db->exec($sql);
            $this->show(message::getJsonMsgStruct('5007'),100);
            exit;
        }
        $this->show(message::getJsonMsgStruct('5008'), $num);
    }

}
