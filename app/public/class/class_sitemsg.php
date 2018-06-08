<?php
/* ClassName: sitemsg
 * Memo:站内信操作类
 * Version:1.0.0
 * EditTime:2015-11-20
 * Writer:adadsa
 * */
require_once( PUBLICROOT . '/class/class_xinge.php' );

class sitemsg {

    static $db = NULL;
    static $dataLoop = array();
    static $dataReplace = '';
    static $result = NULL;

    public function __construct () {
        self::$db = new MySql();
    }

    //获取站内信
    public function get ( $uid = '' ) {
        try {
            self::$db->beginTRAN();
            //7天前的时间
            $day7Time = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );

            if ( $uid == '' ) {
                if ( isset( $_SESSION[ 'userID' ] ) ) {
                    $uid = $_SESSION[ 'userID' ];
                } else {
                    return FALSE;
                }
            }
            //注册时间（会员的ID好32位，雇员的8位）
            if ( strlen( $uid ) == 32 ) {    //会员
                $result = self::$db->getRow( "SELECT u_createTime, u_level FROM t_user WHERE u_id = '" . $uid . "'" );
                if ( !$result ) {
                    return FALSE;
                }
                $regTime = $result[ 'u_createTime' ];
                $userLevel = $result[ 'u_level' ];
                $where = "mbt_targetType = 0
						or (mbt_targetType = 1 and (mbt_targetID = 0 or mbt_targetID = '" . $userLevel . "'))
						or (mbt_targetType = 3 and (mbt_targetID = '" . $uid . "'))";
            } else {    //雇员
                $result = self::$db->getRow( "SELECT e_createTime, e_departmentID FROM t_employee WHERE e_id = '" . $uid . "'" );

                if ( !$result ) {
                    return FALSE;
                }
                $regTime = $result[ 'e_createTime' ];
                $userDepartment = $result[ 'e_departmentID' ];
                $where = "mbt_targetType = 0
						or (mbt_targetType = 2 and (mbt_targetID = 0 or mbt_targetID = '" . $userDepartment . "'))
						or (mbt_targetType = 4 and (mbt_targetID = '" . $uid . "'))";
            }

            //站内信的发布时间要大于7天前或者大于注册时间
            $time = ( $day7Time > $regTime ) ? $day7Time : $regTime;

            $sql = "INSERT INTO `t_mailbox_readed` (mbr_mbID, mbr_uid, mbr_isReaded, mbr_getTime, mbr_readTime, mbr_isDelete, mbr_deleteTime)
					SELECT mbt.mbt_mbID, '" . $uid . "', 0, '" . F::mytime() . "', '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00' FROM `t_mailbox_target` as mbt
					WHERE ( $where )
					AND NOT EXISTS (
						select 1 FROM `t_mailbox_readed` as mbr WHERE mbr_uid = '" . $uid . "' AND mbr.mbr_mbID = mbt.mbt_mbID
					)
					AND NOT EXISTS (
						select 1 FROM `t_mailbox` as mb WHERE mb_ctime < '" . $time . "' AND mb.mb_id = mbt.mbt_mbID
					)
					GROUP BY mbt.mbt_mbID;";
            //echo $sql;die;
            self::$db->exec( $sql );
            self::$db->commitTRAN();
        } catch ( Exception $e ) {
            self::$db->rollBackTRAN();
            return FALSE;
        }
        //return $this;
    }

    //读取站内信
    public function read ( $id, $userID ) {
        try {
            self::$db->beginTRAN();
            $sql = "select mb_id,mb_uid,mb_title,mb_content,mb_annex,mb_ctime,mbr_getTime,mbr_isReaded,mbr_readTime from t_mailbox as mb
					LEFT JOIN
					t_mailbox_readed as mbr ON mb.mb_id=mbr.mbr_mbID
					WHERE mb.mb_id='" . $id . "' and (mb.mb_uid='" . $userID . "' or mbr.mbr_uid='" . $userID . "')";
            //echo $sql;
            $info = self::$db->getRow( $sql );
            if ( $info ) {
                if ( !$info[ 'mbr_isReaded' ] ) {
                    self::$db->update( 't_mailbox_readed', array('mbr_isReaded' => 1, 'mbr_readTime' => F::mytime()), "  mbr_mbID='" . $id . "' and mbr_uid='" . $userID . "'" );
                }
                self::$db->commitTRAN();
                $result = [
                    'id'       => $info[ 'mb_id' ],
                    'uid'      => $info[ 'mb_uid' ],
                    'title'    => $info[ 'mb_title' ],
                    'content'  => $info[ 'mb_content' ],
                    'annex'    => $info[ 'mb_annex' ],
                    'ctime'    => $info[ 'mb_ctime' ],
                    'getTime'  => $info[ 'mbr_getTime' ],
                    'isReaded' => $info[ 'mbr_isReaded' ],
                    'readTime' => $info[ 'mbr_readTime' ],
                    'isSender' => ( $info[ 'mb_uid' ] == $userID ) ? 1 : 0

                ];
                return $result;
            } else {
                return FALSE;
            }
        } catch ( Exception $e ) {
            self::$db->rollBackTRAN();
            return FALSE;
            //print_r($e->getMessage());
        }

    }

    //保存站内信
    public function save ( $data ) {
        try {
            self::$db->beginTRAN();
            if ( !array_key_exists( 'targets', $data ) || !is_array( $data[ 'targets' ] ) ) {
                return FALSE;
            }
            if ( !array_key_exists( 'title', $data ) || $data[ 'title' ] == '' ) {
                return FALSE;
            }
            if ( !array_key_exists( 'content', $data ) || $data[ 'content' ] == '' ) {
                return FALSE;
            }
            if ( !array_key_exists( 'type', $data ) || $data[ 'type' ] == '' ) {
                return FALSE;
            }
            /* 如果指定了发件人 */
            if ( isset( $data[ 'sender' ] ) ) {
                $mb_uid = $data[ 'sender' ];
            } else {
                if ( isset( $_SESSION[ 'userID' ] ) ) {
                    if ( strlen( $_SESSION[ 'userID' ] ) == 32 ) {    //会员
                        $mb_uid = 'dttx00001';//默认指定系统管理员发信
                    } else {//雇员
                        $mb_uid = $_SESSION[ 'userID' ];
                    }
                } else {
                    return FALSE;
                }
                /* else{
                    $mb_uid = '';
                } */
            }

            $saveData = array(
                'mb_uid'     => $mb_uid,
                'mb_ctime'   => F::mytime(),
                'mb_title'   => $data[ 'title' ],
                'mb_content' => $data[ 'content' ],
                'mb_type'    => $data[ 'type' ],
            );
            $row = self::$db->insert( 't_mailbox', $saveData );//添加信息进信息表
            //记录操作日志
            $saveData[ 'memo' ] = '发布新消息';
            log::writeLogMongo( 30302, 't_mailbox', self::$db->getLastID(), $saveData );
            if ( $row ) {//如果添加信息成功,添加信息接收表
                $mb_id = self::$db->getLastID();
                $fields = ['mbt_mbID', 'mbt_targetType', 'mbt_targetID'];
                $values = [];
                $pushContent = strip_tags( html_entity_decode( $data[ 'content' ] ) );
                $iosenv = 1; //1-生产环境，2-测试环境
                $group = '';
                if ( array_key_exists( 'targets', $data ) && is_array( $data[ 'targets' ] ) ) {
                    foreach ( $data[ 'targets' ] as $target ) {
                        $targetArr = explode( '-', $target );
                        $targetArr[ 1 ] = count( $targetArr ) > 1 ? $targetArr[ 1 ] : 0;
                        $values[] = [$mb_id, $targetArr[ 0 ], $targetArr[ 1 ]];

                        //app信鸽推送
                        switch ( $targetArr[ 0 ] ) {//0-全部;1-全部会员;2-全部雇员;3-指定会员;4-指定雇员;//Android Token长度为40位,iOS Token长度为64位
                            case 0:
                                Xinge::PushMessageToAllDevice( $data[ 'title' ], $pushContent,$iosenv );
                                break;
                            case 1:
                                switch ( $targetArr[ 1 ] ) {
                                    case 0: //全部会员
                                        $group = '大唐天下所有会员';
                                        break;
                                    case 1: //消费商会员
                                        $group = '大唐天下消费商会员';
                                        break;
                                    case 3: //创客会员
                                        $group = '大唐天下创客会员';
                                        break;
                                    case 4: //创投会员
                                        $group = '大唐天下创投会员';
                                        break;
                                }
                                break;
                            case 2:
                                $group = '大唐天下所有雇员';
                                break;
                            case 3: //3-指定会员;
                                $token = self::$db->getField( "SELECT u_token FROM t_app_xinge WHERE u_id='{$targetArr[1]}' LIMIT 1" );
                                ( $token && strlen( $token ) == 40 ) ? Xinge::PushTokenAndroid( $data[ 'title' ], $pushContent, $token ) : Xinge::PushTokenIos( $pushContent, $token, $iosenv );
                                break;
                            case 4: //4-指定雇员
                                $token = self::$db->getField( "SELECT device_token FROM t_employee_device WHERE eid='{$targetArr[1]}' LIMIT 1" );
                                ( $token && strlen( $token ) == 40 ) ? Xinge::PushTokenAndroid( $data[ 'title' ], $pushContent, $token ) : Xinge::PushTokenIos( $pushContent, $token, $iosenv );
                                break;
//                            case 5: //使用默认设置推送消息给所有设备ios版
//                                Xinge::PushAllIos( $pushContent, $iosenv );
//                                break;
//                            case 6:  //使用默认设置推送消息给标签选中设备android版
//                                Xinge::PushAllAndroid( $data[ 'title' ], $pushContent );
//                                break;
                        }
                        if ( $group ) {
                            //Ios推送
                            Xinge::PushTagIos( $pushContent, $group, $iosenv );

                            //Android推送
                            Xinge::PushTagAndroid( $data[ 'title' ], $pushContent, $group );
                        }
                    }
                    self::$db->inserts( 't_mailbox_target', $fields, $values );//添加信息进信息接收表
                }
            }

            self::$db->commitTRAN();
            return TRUE;
        } catch ( Exception $e ) {
            self::$db->rollBackTRAN();
            return FALSE;
        }
    }
}

?>
