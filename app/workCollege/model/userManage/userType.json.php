<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/22
 * Time: 17:27
 */
 class userType_json extends worker{

    function __construct($options) {
        parent::__construct($options, [609]);
    }

    function run() {
		$dataTable = new DataTables();

        $columns = array(
            array(
                'db' => 'u_code',
                'dt' => 'DT_RowId',
                'formatter' => function( $d, $row ) {
                    return 'row_' . $d;
                }
            ),
            array('db' => 'u_id',	 		     'dt' => 'id'),
            array('db' => 'u_nick',			 	 'dt' => 'nick'),
            array('db' => 'u_name',			 	 'dt' => 'name'),
            array('db' => 'u_sex',			 	 'dt' => 'sex'),
            array('db' => 'u_createTime',	     'dt' => 'ctime'),
            array('db' => 'u_type',			 	 'dt' => 'type'),
            array('db' => 'u_level',			 'dt' => 'level'),
            array('db' => 'u_isUnionSeller',	 'dt' => 'isUnion'),
            array('db' => 'u_state',			 'dt' => 'state'),
            array('db' => 'u_auth',			 	 'dt' => 'auth'),
            array('db' => 'u_code',			 	 'dt' => 'code'),
            array('db' => 'u_fCode',			 'dt' => 'fCode'),
        );


        $sql = "select ### from t_user where 1";
        $result = $dataTable->create($this->options, $sql, $columns);

        echo json_encode($result);
    }
}