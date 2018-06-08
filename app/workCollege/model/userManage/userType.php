<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/22
 * Time: 17:26
 */
class userType extends worker{
    function __construct($options) {
        parent::__construct($options, [609]);
    }

    function run() {
        $user = isset($this->options['user']) ? $this->options['user'] : '';

        $info = array(
            'authList' => array(
                '0' => array(
                    'mobile' => [0],
                    'email'	 => [1],
                    'person' => [2,3,4,5,6],
                ),
                '1' => array(
                    'mobile'  => [0],
                    'email'	  => [1],
                    'company' => [2,3,4,5,6,7,8,9,10,11,12],
                )
            ),
        );

        $data = array(
            'jsData' 		=> json_encode($info),
            'code'          => 609,
            'tempId'		=> 'temp_'.F::getGID(),
        );

        $this->setReplaceData($data);
        $this->setTempAndData();
        $this->show();
    }
}