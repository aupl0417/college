<?php

class saomaLogin_json extends guest {

    function run () {
        //扫码登陆数据处理
        $cache = new cache();
        $cacheId = 'sm_' . md5( session_id() . 'ydw2016' );
        $cacheData = $cache->get( $cacheId );

        if ( $cacheData[ 'userID' ] ) {
            //用户登录
            $db = new MySql();
            $user = new user( $db );
            $userInfo = $user->getFullUserInfo( $cacheData[ 'userID' ] );

            if ( $userInfo[ 'u_powerList' ] != 'all' ) {
                $userPowerList = $userInfo[ 'u_powerList' ];
                $userPowerHash = $userInfo[ 'u_powerHash' ];
                if ( !F::checkPowerHash( $userPowerList, $userPowerHash ) ) {
                    return '2113';
                }
                if ( $userInfo[ 'u_level' ] > 0 ) {
                    $sql = 'SELECT ul_powerList,ul_powerHash FROM t_user_level WHERE ul_id = ' . $userInfo[ 'u_level' ];
                    $ret = $db->getRow( $sql );
                    if ( F::checkPowerHash( $ret[ 'ul_powerList' ], $ret[ 'ul_powerHash' ] ) ) {
                        $userPowerList .= ',' . $ret[ 'ul_powerList' ]; //加上级别权限
                    }
                }
                //获取所属分类的权限列表
                if ( $userInfo[ 'u_groupId' ] > 0 ) {
                    $groupPowerList = group::getGroupPower( 1 );
                    $userPowerList .= ',' . $groupPowerList; //加上组权限
                }
            } else {
                $userPowerList = '';
                $sql = 'SELECT p_id FROM t_power';
                $ret = $db->getAll( $sql );
                foreach ( $ret as $v ) {
                    $userPowerList .= ',' . $v[ 'p_id' ];
                }
                $userPowerList = $userPowerList . ',';
            }
            //等级权限//暂无
            //去掉权限中的重复值和空值
            $powerArray = explode( ',', $userPowerList );
            $powerArray = array_unique( $powerArray );
            $key = array_search( '', $powerArray );
            if ( F::isNotNull( $key ) ) {
                unset( $powerArray[ $key ] );
            }
            $userPowerList = implode( ',', $powerArray );


            $_SESSION[ 'userID' ] = $userInfo[ 'u_id' ];
            $_SESSION[ 'userNick' ] = $userInfo[ 'u_nick' ];
            $_SESSION[ 'userLogo' ] = $userInfo[ 'u_logo' ];
            $_SESSION[ 'userLevel' ] = $userInfo[ 'u_level' ];
            $_SESSION[ 'userDepartment' ] = 0;
            $_SESSION[ 'userType' ] = 1;
            $_SESSION[ 'userPwdMd5' ] = $userInfo[ 'u_loginPwd' ];
            $_SESSION[ 'userPower' ] = $userPowerList;
            $_SESSION[ 'userState' ] = $userInfo[ 'u_state' ];
            $_SESSION[ 'userFcode' ] = $userInfo[ 'u_fCode' ];
            $_SESSION[ 'userCode' ] = $userInfo[ 'u_code' ];
            $_SESSION[ 'userPhone' ] = $userInfo[ 'u_tel' ];
            $_SESSION[ 'userClass' ] = $userInfo[ 'u_type' ];
            $this->show( message::getJsonMsgStruct( '1001', '成功' ) );
            exit;
        }
        $this->show( message::getJsonMsgStruct( '1002', '失败' ) );
        exit;
    }

}
