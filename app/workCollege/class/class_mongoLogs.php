<?php
/**
 * 用户操作日志(monogodb logs)
 * Created by PhpStorm.
 * User: JoJoJing
 * Date: 2016/8/10
 * Time: 11:48
 */
class mongoLogs{
    private $db = null;

    private $mgdb = null;

    public function __construct($db = NULL, $mgdb = NULL)
    {
        $this->db = is_null($db) ? new MySql() : $db;
        $this->mgdb = is_null($mgdb) ? new mgdb() : $mgdb;
    }

    /**
     * 输出符合查询条件的日志记录
     * @param $options['start']  array 开始查询位置 $options['length'] int 当前页面显示记录数 $options['search'] array datatable 查询条件
     * @param array $inWhere    array($value1,$value2,.....) in查询参数
     * @param array $wheres     array('field' => array('value' => $value)) 条件查询
     * @param array $orWhere    array('field' => $value) or查询参数
     * @param array $likeWhere  array() like模糊查询参数
     * @param int $type default 0（0 返回当前页面记录数；1 返回符合查询条件的记录数）
     * @return array
    */
    public function logList($options,$inWhere=array(),$wheres=array(),$orWhere=array(),$likeWhere=array(),$type=0){
        $order = array('log_time'=>-1);
        $length = !isset($options['length']) ? 10 : $options['length'];
        $start  = !isset($options['start']) ? 0 : $options['start'];

        if(isset($options['search'])){
            foreach($options['search'] as $k => $v){
                switch($k){
                    case 'log_user'://操作者
                        $id = $this->db->getField("select u_id from t_user where u_nick = '".$v['value']."'");
                        if(empty($id)){
                            $id = $this->db->getField("select e_id from t_employee where e_name = '".$v['value']."'");
                        }
                        $where_user = array(
                            'log_user'      => array('value' => $id),
                        );
                        break;
                    case 'log_time'://创建时间
                        if(array_key_exists('1', $v)){//如果传来了两个参数
                            $minDate = strtotime($v[0]['value']);
                            $maxDate = strtotime($v[1]['value']);
                        }else{//如果只传来了一个参数
                            if($v['filter'] == 'gte'){//最小值
                                $minDate = strtotime($v['value']);
                                $maxDate = time();//$minDate + 30 * 86400;
                            }else{//最大值
                                $maxDate = strtotime($v['value']);
                                $minDate = 0;//$maxDate - 30 * 86400;
                            }
                        }
                        $between = array(
                            'key'     => 'log_time',
                            'minDate' => $minDate,
                            'maxDate' => $maxDate,
                        );
                        break;
                    default:
                        $where[$k] = [
                            'value' => $v['value'],
                            'num' => $v['num'],
                        ];
                        break;
                }
            }
        }

        if($type == 0){
            $where = $this->mgdb->limit($length)->offset($start)->orderBy($order);
        }else{
            $where = $this->mgdb;
        }

        if(isset($inWhere) && !empty($inWhere)){
            $where = $where->whereIn('log_type_id',$inWhere);
        }
        if(isset($orWhere) && !empty($orWhere)){
            $where = $where->orWhere($orWhere);
        }
        if(isset($likeWhere) && !empty($likeWhere)){
            $where = $where->whereLike('log_type_id',$likeWhere);
        }
        if(isset($wheres) && !empty($wheres)){
            $where = $where->where($wheres);
        }
        if(isset($where_user) && !empty($where_user)){
            $where = $where->where($where_user);
        }
        if (isset($between)) {
            $where = $where->whereBetweenNe($between['key'],$between['minDate'],$between['maxDate']);
        }

        $list = $where->get('logs');
        return $list;
    }

    /**总记录数
     * @param $options
     * @param array $inWhere
     * @param array $wheres
     * @param array $orWhere
     * @param array $likeWhere
     * @return int
     */
    public function countLog($options,$inWhere=array(),$wheres=array(),$orWhere=array(),$likeWhere=array()){
        $num = $this->logList($options,$inWhere,$wheres,$orWhere,$likeWhere,1);
        return count($num);
    }

    /**
     * 处理log_change数据-详情
     * @param int $logTypeId
     * @param array $arr
     * @return string $str
     */
    function logTemplate($logTypeId,$arr){
        $str = '';
        $str .= '<table class="table responsive-form-table">';
        $str .= '    <tbody>';
        $logArr = $arr;
        $arr['log_change'] = isset($arr['log_change']) ? $arr['log_change'] : '';
        $arr = $arr['log_change'];
        if(!empty($arr)){
            switch($logTypeId){
                case 20160003: //个人会员注册
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">身份类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="身份类型">'.($arr['u_type'] == 0 ? '个人会员' : '企业会员').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="昵称">'.$arr['u_nick'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">手机号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="手机号">'.$arr['u_tel'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">推广码 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="推广码">'.$arr['u_fCode'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 20160004: //企业会员注册
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">身份类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="身份类型">'.($arr['u_type'] == 1 ? '企业会员' : '个人会员').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="昵称">'.$arr['u_nick'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">手机号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="手机号">'.$arr['u_tel'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">推广码 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="推广码">'.$arr['u_fCode'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">公司名称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="公司名称">'.$arr['u_companyName'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">营业执照编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="营业执照编号">'.$arr['u_comLicenseCode'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">公司组织机构类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="公司组织机构类型">'.$arr['u_organize'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 20160002: //个人资料完善
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">用户名 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_name'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后一次更新时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="最后一次更新时间">'.$arr['u_lastUpdateTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">身份证类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="身份证类型">'.($arr['u_certType'] == 0 ? '个人会员' : '企业会员').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">国家 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="国家">'.$arr['u_country'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">证件号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="证件号">'.$arr['u_certNum'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">个人资料完善</td>';
                    $str .= '		</tr>';
                    break;

                case 502: //认证
                    $str .= '';
                    if(!isset($arr['au_type'])){
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">认证状态 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="认证状态">'.(isset($arr['u_auth']) ? $arr['u_auth'] : '-').'</td>';
                        $str .= '		</tr>';
                        if(isset($arr['u_tel'])){
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">手机号 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="手机号">'.$arr['u_tel'].'</td>';
                            $str .= '		</tr>';
                        }
                        if(isset($arr['u_email'])){
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">邮箱号码 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="邮箱号码">'.$arr['u_email'].'</td>';
                            $str .= '		</tr>';
                        }
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                        $str .= '		</tr>';
                    }else{
                        if($arr['au_type'] == 2){
                            if($arr['au_utype'] == 0){
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员昵称">'.$arr['au_nick'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">会员名 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员名">'.$arr['u_name'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">英文姓名/别名/曾用名英文姓名/别名/曾用名 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="英文姓名">'.$arr['u_englishName'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">身份类型(0-个人;1-企业) :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="身份类型">'.$arr['au_utype'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">申请认证时间 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="申请认证时间">'.$arr['au_ctime'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">认证状态 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="认证状态">'.$arr['au_ctime'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">认证备注 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="认证备注">'.$arr['au_memo'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">用户身份证正面 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户身份证正面"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_imgs_1'].'">'.$arr['au_imgs_1'].'</a></td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">用户身份证反面 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_imgs_2'].'"></a>'.$arr['au_imgs_2'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">用户手持身份证 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_imgs_3'].'">'.$arr['au_imgs_3'].'</a></td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">证件类型(0大陆;1非大陆) :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_certType'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">证件号 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_certNum'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">区域编号 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_area'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">所在地区 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_address'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">行业编号 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_indId'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">从事行业 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_industry'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                                $str .= '		</tr>';
                            }else{
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">法人 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_comLegalName'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">法人身份证正面 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['1'].'">'.$arr['1'].'</a></td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">法人身份证反面 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['2'].'">'.$arr['2'].'</a></td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">法人手持身份证 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['3'].'">'.$arr['3'].'</a></td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">营业执照副本 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['4'].'">'.$arr['4'].'</a></td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">税务登记证 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['5'].'">'.$arr['5'].'</a></td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">企业类型 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_companyType'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">三证合一(0否;1是) :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_companyThree'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">分公司(0否;1是) :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_isBranch'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">企业组织机构类型 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_organize'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">区域编号 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_comArea'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">行业编码 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_comIndid'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">申请认证时间 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_comUpdateTime'].'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">税务登记证 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['u_comTaxCode']) ? $arr['u_comTaxCode'] : '-').'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">组织机构代码证 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['u_comOrgCode']) ? $arr['u_comOrgCode'] : '-').'</td>';
                                $str .= '		</tr>';
                                $str .= '		<tr class="table-row">';
                                $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                                $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                                $str .= '		</tr>';
                            }
                        }else{
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_nick'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">负责人姓名 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_comLeadName'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">经营地址 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_comAddress'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">行业热门标签 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_comTagse'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">行业热门标签名 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_comTagsName'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">公司主营业务 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_comMainIndustry'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">企业行业编码 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_comIndid'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">企业行业 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_industry'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">公司所在区域编码 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_comArea'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">公司所在区域 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_address'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">店铺名称 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_shopName'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">成为联盟商家时间 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_unionTime'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">企业资料修改时间 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_comUpdateTime'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">认证状态(2认证中,1已认证，0未认证) :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_result'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">认证申请时间 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['au_ctime'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">图片1 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_authImg']['img-1'].'">'.$arr['img-1'].'</a></td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">图片2 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_authImg']['img-2'].'">'.$arr['img-2'].'</a></td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">图片3 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_authImg']['img-3'].'">'.$arr['img-3'].'</a></td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">图片4 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_authImg']['img-4'].'">'.$arr['img-4'].'</a></td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">图片5 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_authImg']['img-5'].'">'.$arr['img-5'].'</a></td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">图片6 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_authImg']['img-6'].'">'.$arr['img-6'].'</a></td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">图片7 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_authImg']['img-7'].'">'.$arr['img-7'].'</a></td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">图片8 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a href="https://image.dttx.com/v1/tfs/'.$arr['au_authImg']['img-8'].'">'.$arr['img-8'].'</a></td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['au_memo']) ? $arr['au_memo'] : '-').'</td>';
                            $str .= '		</tr>';
                        }
                    }
                    break;

                case 1112: //用户登录
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">用户名 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['username'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">登录设备 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['iswap'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 50101: //会员资料维护
					$uNick = $this->db->getField("select u_nick from t_user where u_id = '".$logArr['log_user']."'");
					$str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员昵称">'.$uNick.'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原生日 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="原生日">'.(isset($arr['u_birth']) ? $arr['u_birth'] : '').'  -->'.(isset($arr['year']) ? $arr['year'] : '').'-'.(isset($arr['month']) ? $arr['month'] : '').'-'.(isset($arr['day']) ? $arr['day'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原学历 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="原学历">'.(isset($arr['u_eduID']) ? $arr['u_eduID'] : '').'  -->'.(isset($arr['education']) ? $arr['education'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原政治面貌 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="原政治面貌">'.(isset($arr['u_political']) ? $arr['u_political'] : '').'  -->'.(isset($arr['political']) ? $arr['political'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原社会背景 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="原社会背景">'.(isset($arr['u_social']) ? $arr['u_social'] : '').'  -->'.(isset($arr['social']) ? $arr['social'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原详细地址 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="原详细地址">'.(isset($arr['u_address']) ? $arr['u_address'] : '').'  -->'.(isset($arr['address']) ? $arr['address'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原邮编 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="原邮编">'.(isset($arr['u_postage']) ? $arr['u_postage'] : '').'  -->'.(isset($arr['postage']) ? $arr['postage'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原联系电话 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="原联系电话">'.(isset($arr['u_otherTel']) ? $arr['u_otherTel'] : '').'  -->'.(isset($arr['otherTel']) ? $arr['otherTel'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原QQ :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="原QQ">'.(isset($arr['u_qq']) ? $arr['u_qq'] : '').'  -->'.(isset($arr['qq']) ? $arr['qq'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '').'</td>';
                    $str .= '		</tr>';
                    break;

                case 50102: //安全设置
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">设置时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="设置时间">'.$arr['u_lastUpdateTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 50801: //会员发起工单
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_uid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">工单发起人 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_unick'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">问题描述 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['fu_reason'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">上传文件 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a target="_blank" href="https://image.dttx.com/v1/tfs/'.explode('|',$arr['fu_file'])[0].'">'.explode('|',$arr['fu_file'])[0].'</a><br /><a target="_blank" href="https://image.dttx.com/v1/tfs/'.explode('|',$arr['fu_file'])[1].'">'.explode('|',$arr['fu_file'])[1].'</a></td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">当前步骤 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_currentStep'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后操作的步骤 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_lastStep'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后步骤的操作用户 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_lastStepEid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后操作步骤的时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_lastStepTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后步骤的状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_lastStepState'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">费用支付方式（1-余额 2-唐宝） :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_payManner'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 10211: //商家设置库存积分提醒功能
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">库存积分下限 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['store'].'</td>';
                    $str .= '		</tr>';
                    if(isset($arr['manner']['email'])){
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">是否已提醒(0否,1是) :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['manner']['email']['reminded'].'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">上一次邮箱提醒时间 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['manner']['email']['lastRemindTime'].'</td>';
                        $str .= '		</tr>';
                    }
                    if(isset($arr['manner']['mobile'])){
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">是否已提醒(0否,1是) :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['manner']['mobile']['reminded'].'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">上一次手机提醒时间 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['manner']['mobile']['lastRemindTime'].'</td>';
                        $str .= '		</tr>';
                    }
                    if(isset($arr['manner']['sysMsg'])){
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">是否已提醒(0否,1是) :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['manner']['sysMsg']['reminded'].'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">上一次站内信提醒时间 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['manner']['sysMsg']['lastRemindTime'].'</td>';
                        $str .= '		</tr>';
                    }
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">提醒方式 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['manner'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">提醒次数 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['remindTimes'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 605: //雇员-联盟商家申请
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['uc_uid'] == 0 ? '否' : '是').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['username']) ? $arr['username'] : '-').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">是否签订合同 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['uc_validateContract'] == 0 ? '否' : '是').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">是否购买牌匾 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['uc_isBuyTablet'] == 0 ? '否' : '是').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">合同编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_contractCode'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">快递公司 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_expressCompany'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">快递单号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_expressNo'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">牌匾编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_tabletCode'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态(-1未通过;0申请中;1通过) :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_state'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">购买牌匾时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_buyTabletTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">合同签订时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_validateContractTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">申请开始时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_ctime'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 60502: //雇员-联盟商家审核
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">是否签订合同 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['uc_validateContract'] == 0 ? '否' : '是').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">是否购买牌匾 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['uc_isBuyTablet'] == 0 ? '否' : '是').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">合同编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_contractCode'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">快递公司名称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_expressCompany'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">快递单号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_expressNo'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">牌匾编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_tabletCode'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态(-1未通过;0申请中;1通过) :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_state'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">拒绝原因 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_reject'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">购买牌匾时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_buyTabletTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">合同签订时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_validateContractTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">处理申请时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['uc_handleTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 30601: //雇员-雇员发起工单
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">工单分类 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['fu_fid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_uid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_unick'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">问题描述 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['fu_reason'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">上传文件 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名"><a target="_blank" href="https://image.dttx.com/v1/tfs/'.explode('|',$arr['fu_file'])[0].'">'.explode('|',$arr['fu_file'])[0].'</a><br /><a target="_blank" href="https://image.dttx.com/v1/tfs/'.explode('|',$arr['fu_file'])[1].'">'.explode('|',$arr['fu_file'])[1].'</a></td>';
                    $str .= '		</tr>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作人 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_createEid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">当前步骤 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_currentStep'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后操作的步骤 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_lastStep'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后步骤的操作用户 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_lastStepEid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后操作步骤的时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_lastStepTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后步骤的状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['flow_lastStepState'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 60102: //雇员-冻结
					$state = array(
						'0' => '冻结',
						'1' => '正常',
						'-1' => '停止使用',
						'2' => '临时',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_nick'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$state[$arr['u_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['u_lastUpdateTime']) ? $arr['u_lastUpdateTime'] : $arr['u_updateTime']).'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 60103: //雇员-解冻
					$state = array(
						'0' => '冻结',
						'1' => '正常',
						'-1' => '停止使用',
						'2' => '临时',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_nick'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$state[$arr['u_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['u_lastUpdateTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;
					
				case 7010101: //雇员-冻结账户
					$state = array(
						'0' => '冻结',
						'1' => '正常',
						'-1' => '注销',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">账户ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['a_id'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$state[$arr['a_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作雇员 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['employId'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['updateTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作原因 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['reason'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;
					
				case 7010102: //雇员-解冻账户
					$state = array(
						'0' => '冻结',
						'1' => '正常',
						'-1' => '注销',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">账户ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['a_id'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$state[$arr['a_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作雇员 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['employId'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['updateTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作原因 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['reason'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 60105: //雇员-重置密码
					$id = $logArr['log_r_id'];
					$uNick = $this->db->getField("select u_nick from t_user where u_id = '".$id."'");
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员昵称">'.$uNick.'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;
					
				case 95083: //雇员-重置密码，短信通道日志
					$tel = $logArr['log_r_id'];
					$uNick = $this->db->getField("select u_nick from t_user where u_tel = '".$tel."'");
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员昵称">'.$uNick.'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.preg_replace('/[0-9]{6,8}/','********',$logArr['log_change']).'</td>';
                    $str .= '		</tr>';
                    break;

                case 60113: //雇员-会员降级
					$uType = array(
						'1' => '消费商会员',
						'3' => '创客会员',
						'4' => '创投会员',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">是否降级 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['u_lowergrade'] == 1 ? '是' : '否').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">原等级 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$uType[$arr['old_level']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">现等级 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$uType[$arr['u_level']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 10304:  //购买代理
					$aState = array(
						'1' => '待定价',
						'2' => '待付款',
						'3' => '已付款',
						'4' => '合同签订中',
						'5' => '合同已签订',
						'6' => '购买成功',
						'7' => '已退款',
						'8' => '已关闭',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">工单类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="工单类型">'.$arr['fag_fid'].'-申请购买代理</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">用户ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户ID">'.$arr['flow_uid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">EA/DA类型（ABCDEF） :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="EA/DA类型">'.$arr['fag_type'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">代理区域 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="代理区域">'.$arr['fag_areaId'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">代理行业 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="代理行业">'.$arr['fag_indId'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">所属代理公司编码 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="所属代理公司编码">'.$arr['fag_comId'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">职务 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="职务">'.$arr['fag_duty'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">代理级别 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="代理级别">'.$arr['fag_level'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">价格 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="价格">'.$arr['fag_price'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">当前步骤 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="当前步骤">'.$arr['flow_currentStep'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后操作的步骤 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="最后操作的步骤">'.$arr['flow_lastStep'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后步骤的操作用户 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="最后步骤的操作用户">'.$arr['flow_lastStepEid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后步骤的状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="最后步骤的状态">'.$arr['flow_lastStepState'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">代理状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="代理状态">'.$aState[$arr['flow_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">申请时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="申请时间">'.$arr['flow_createTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后操作步骤的时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="最后操作步骤的时间">'.$arr['flow_lastStepTime'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 40102:  //提现
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">账户 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="账户">'.$arr['cardID'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">到账时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="到账时间">'.$arr['day'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">提现金额 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="提现金额">'.$arr['money'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 40104:  //唐宝兑换
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员昵称">'.$arr['userNick'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">兑换唐宝 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="兑换唐宝">'.$arr['tangbao'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 40106:  //银行转账
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员ID">'.$arr['bt_uid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">转账银行 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="转账银行">'.$arr['bt_bankID'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">卡号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="卡号">'.$arr['bt_cardNum'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">户名 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="户名">'.$arr['bt_cardMaster'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">转账金额 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="转账金额">'.$arr['bt_money'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">转账凭证 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="转账凭证">'.$arr['bt_image'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="状态">'.$arr['bt_state'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">创建时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="创建时间">'.$arr['bt_createTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 40201:  //添加、删除支付账户（银行卡）
					$axType = array(
						'1' => '银行储蓄卡',
						'2' => '银行信用卡',
						'3' => '支付宝',
						'4' => '财付通',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">卡类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="卡类型">'.$axType[$arr['ax_type']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">银行代码 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="银行代码">'.$arr['ax_bankid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">银行名称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="银行名称">'.$arr['ax_bankname'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">开户行省 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="开户行省">'.$arr['ax_province'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">开户行市 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="开户行市">'.$arr['ax_city'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">默认支付 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="默认支付">'.($arr['ax_isDefault'] == 1 ? '是' : '否').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">开户行支行名称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="开户行支行名称">'.$arr['ax_cardaddr'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">户主 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="户主">'.$arr['ax_cardmaster'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">账号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="账号">'.$arr['ax_account'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 40202:  //添加、删除支付账户（第三方平台）
					$axType = array(
						'1' => '银行储蓄卡',
						'2' => '银行信用卡',
						'3' => '支付宝',
						'4' => '财付通',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">卡类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$axType[$arr['ax_type']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">第三方平台 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ax_bankid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">第三方名称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ax_bankname'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">默认支付 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['ax_isDefault'] == 1 ? '是' : '否').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">户主 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ax_cardmaster'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">账号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ax_account'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 70202:  //在线充值 设置为已到账
					$state = array(
						'0' => '未到账',
						'1' => '成功',
						'-1' => '撤销',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作雇员id :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="操作雇员id">'.$arr['ci_operId'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="操作时间">'.$arr['ci_operTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">充值成功的异动id :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="充值成功的异动id">'.$arr['ci_transId'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="状态">'.$state[$arr['ci_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 713:  //线下银行卡转账审核
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">操作雇员id :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bt_eid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">现金异动id :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bt_caid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">雇员操作时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bt_arriveTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">拒绝理由 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['bt_reason']) ? $arr['bt_reason'] : '').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 705:  //资金流转
					$aType = array(
						'1' => '余额',
						'2' => '冻结资金',
						'3' => '积分',
						'4' => '唐宝',
						'5' => '库存积分',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">异动ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['id'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ul'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">账户类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$aType[$arr['sale_status']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">账号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['toFlag'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['p2'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">金额 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['money'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">订单编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['orderid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 10208:  //商家积分分发
					$state = array(
						'0' => '待付款',
						'1' => '已付款',
						'2' => '已发货',
						'3' => '已收货,交易完成',
						'4' => '待赠送积分',
						'5' => '已赠送积分',
						'6' => '已退款',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">订单编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_id'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">交易类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_type'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">订单总金额 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_money'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">买家ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_buyUid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">卖家ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_sellUid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">交易记录创建时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_createTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">全返比例 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_returnPercent'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$state[$arr['bu_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">是否已赠送积分 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['bu_isQF'] == 0 ? '否' : '是').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 10207:  //购买库存积分
					$state = array(
						'0' => '待付款',
						'1' => '已付款',
						'2' => '已发货',
						'3' => '已收货,交易完成',
						'4' => '待赠送积分',
						'5' => '已赠送积分',
						'6' => '已退款',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">订单编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_id'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">交易类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_type'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">支付类型(1余额2唐宝3现金) :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_payType'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">订单总金额 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_money'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">买家ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_buyUid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">卖家ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_sellUid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">交易记录创建时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_createTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">全返比例 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_returnPercent'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$state[$arr['bu_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">是否已赠送积分 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.($arr['bu_isQF'] == 0 ? '否' : '是').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['bu_memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 40101:  //充值
					$payState = array(
						'1' => '微信',
						'2' => '支付宝',
						'5' => '工行POS',
						'6' => '微赢微信',
						'7' => '微赢支付宝',
						'8' => '银联',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">异动记录 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ci_caid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">会员昵称 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ci_userNick'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">充值时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ci_createTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">充值类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$payState[$arr['ci_payType']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">金额 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ci_money'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态(0默认1成功-1撤销) :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['ci_state'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户名">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 20205:  //添加雇员
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">雇员ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="雇员ID">'.$arr['e_id'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">雇员姓名 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="雇员姓名">'.$arr['e_name'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">雇员身份证 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="雇员身份证">'.$arr['e_certNum'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">部门编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="部门编号">'.$arr['e_departmentID'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">职务编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="职务编号">'.$arr['e_dutyID'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">添加时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="添加时间">'.$arr['e_createTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">入职时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="入职时间">'.$arr['e_joinTime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态(0冻结1在职-1离职) :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="状态">'.$arr['e_state'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">姓名拼音 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="姓名拼音">'.$arr['e_charName'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">最后登录ip :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="最后登录ip">'.$arr['e_logIp'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 20203:  //修改雇员权限
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">被修改雇员 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="雇员ID">'.$logArr['log_r_id'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 30302:  //发布消息
					$mbType = array(
						'1' => '通知消息',
						'2' => '交易消息',
						'3' => '活动消息',
						'4' => '我的资产',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">发布者 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="发布者">'.$arr['mb_uid'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">发布时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="发布时间">'.$arr['mb_ctime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">消息主题 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="消息主题">'.$arr['mb_title'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">消息内容 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="消息内容">'.F::TextToHtml($arr['mb_content']).'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">消息类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="消息类型">'.$mbType[$arr['mb_type']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                case 10201:  //下载提现表格
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 404:  //系统参数修改
                    $sysName = $this->db->getField("select sys_memo from t_system");
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">参数 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="参数">'.$sysName.'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">参数值 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="参数值">'.$arr['sys_value'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 401:  //系统参数修改
                    $riName = $this->db->getField("select ri_meno from t_company_reward_rule");
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">业务参数 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="业务参数">'.$riName.'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">等级1 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="等级1">'.$arr['ri_L1'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">等级2 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="参数值">'.$arr['ri_L2'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">等级3 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="参数值">'.$arr['ri_L3'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">等级4 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="参数值">'.$arr['ri_L4'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">等级5 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="参数值">'.$arr['ri_L5'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">等级6 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="参数值">'.$arr['ri_L6'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">等级7 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="参数值">'.$arr['ri_L7'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 30402:  //意见/BUG反馈处理
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">处理工作人员 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="处理工作人员">'.$arr['feed_eid']   .'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">处理状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="处理状态">'.($arr['feed_state'] == 1 ? '已处理' : '待处理').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">处理结果 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="处理结果">'.$arr['feed_result'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">处理时间 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="处理时间">'.$arr['feed_etime'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">回复方式 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="回复方式">'.$arr['feed_back'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 30403:  //是否前端显示bug反馈
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">信息ID :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="信息ID">'.$arr['id'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">显示方式 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="显示方式">'.($arr['selectType'] == 1 ? '显示' : '隐藏').'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                    $str .= '		</tr>';
                    break;

                case 30801:  //发布，编辑下载信息
                    $nType = array(
                        '15010000' => '申请表',
                        '15020000' => '合同',
                        '15030000' => '设计素材',
                        '15040000' => '教材',
                    );
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">所属类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="发布者">'.$nType[$arr['n_type']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">文件名 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="文件名">'.$arr['n_title'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">下载链接 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="下载链接">'.$arr['n_fileUrl'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">类型 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="类型">'.$arr['n_fileType'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">大小（kb） :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="大小">'.$arr['n_fileSize'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">内容 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="内容">'.F::TextToHtml($arr['n_content']).'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;
					
				case 30803:  //发布app版本/删除已发布APP版本
                    if(isset($arr['v_type'])){
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">终端类型 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="终端类型">'.($arr['v_type'] == 1 ? 'android' : 'ios').'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">版本号 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="版本号">'.$arr['v_version'].'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">版本名称 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="版本名称">'.$arr['v_name'].'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">是否强制更新 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="是否强制更新">'.($arr['v_isForce'] == 1 ? '是' : '否').'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">下载地址 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="下载地址">'.$arr['v_url'].'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">更新内容 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="更新内容">'.F::TextToHtml($arr['v_content']).'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
						$str .= '		</tr>';
					}else{
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">版本id :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="版本id">'.$arr['id'].'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
						$str .= '		</tr>';
					}
                    
					break;
					
				case 40702:  //添加推广系数
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">系数类型 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="系数类型">'.$arr['pf_type'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">说明 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="说明">'.$arr['pf_memo'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">系数值 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="系数值">'.$arr['pf_val'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
					$str .= '		</tr>';
					break;
					
				case 40603:  //行政区域修改
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">区域名称 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="区域名称">'.$arr['a_name'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">GDP值 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="GDP值">'.$arr['a_gdp'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">相对级别 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="相对级别">'.$arr['a_level'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
					$str .= '		</tr>';
					break;
					
				case 40602:  //增加下一级区域
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">区域编码 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="区域编码">'.$arr['a_code'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">区域名称 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="区域名称">'.$arr['a_name'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">父键 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="父键">'.$arr['a_fkey'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">GDP值 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="GDP值">'.$arr['a_gdp'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">相对级别 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="相对级别">'.$arr['a_level'].'</td>';
					$str .= '		</tr>';
					$str .= '		<tr class="table-row">';
					$str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
					$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
					$str .= '		</tr>';
					break;	
					
				case 40604:  //修改区域编码
					if(isset($arr['u_id'])){
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">用户ID :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="用户ID">'.$arr['u_id'].'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
						$str .= '		</tr>';
					}else{
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">修改后的编码 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="修改后的编码">'.(isset($arr['a_code']) ? $arr['a_code'] : $arr['ae_code']).'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">父键 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="父键">'.(isset($arr['a_fkey']) ? $arr['a_fkey'] : $arr['ae_fkey']).'</td>';
						$str .= '		</tr>';
						$str .= '		<tr class="table-row">';
						$str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
						$str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
						$str .= '		</tr>';
					}
					break;	
					
				case 2010202:  //修改雇员信息
					$eState = array(
						'0' => '冻结',
						'1' => '在职',
						'-1' => '离职',
					);
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">雇员姓名 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="雇员姓名">'.$arr['e_name'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">电话 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="电话">'.$arr['e_tel'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">雇员身份证 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="雇员身份证">'.$arr['e_certNum'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">职务编号 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="职务编号">'.$arr['e_dutyID'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="状态">'.$eState[$arr['e_state']].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">姓名拼音 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="姓名拼音">'.$arr['e_charName'].'</td>';
                    $str .= '		</tr>';
                    $str .= '		<tr class="table-row">';
                    $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                    $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.$arr['memo'].'</td>';
                    $str .= '		</tr>';
                    break;

                default:
                    if(in_array($logTypeId,[503,1020209,4020209,1020201,1020202,4020201,4020202])){  //会员升级
                        if(!isset($arr['bu_id'])){
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : $arr).'</td>';
                            $str .= '		</tr>';
                        }else{
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">会员ID :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员ID">'.$arr['bu_id'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">交易类型 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="交易类型">'.$arr['bu_type'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">升级金额 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="升级金额">'.$arr['bu_money'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">商家ID :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="商家ID">'.$arr['bu_sellUid'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">升级时间 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="升级时间">'.$arr['bu_createTime'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">是否全返 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="是否全返">'.($arr['bu_returnPercent'] == 1 ? '是' : '否').'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">状态 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="状态">'.$arr['bu_state'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">支付类型 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="支付类型">'.$arr['bu_payType'].'</td>';
                            $str .= '		</tr>';
                            $str .= '		<tr class="table-row">';
                            $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                            $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : (isset($arr['bu_memo']) ? $arr['bu_memo'] : '-')).'</td>';
                            $str .= '		</tr>';
                        }
                    }elseif(in_array($logTypeId,[60201,60202])){  //个人认证，企业认证审核
						$uNick = '-';
						if(isset($arr['u_id'])){
							$uNick = $this->db->getField("select u_nick from t_user where u_id = '".$arr['u_id']."'"); //会员昵称
						}
						$str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">会员 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员">'.$uNick.'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">认证状态 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="认证状态">'.$arr['u_auth'].'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">审核时间 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="审核时间">'.$arr['u_lastUpdateTime'].'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                        $str .= '		</tr>';
                    }elseif(substr($logTypeId,0,5) == 60110){  //雇员-修改会员资料
						$id = $logArr['log_r_id'];
						$uNick = $this->db->getField("select u_nick from t_user where u_id = '".$id."'"); //会员昵称
						
						$str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">会员 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="会员">'.$uNick.'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">修改前 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="修改前">'.$arr['oldValue'].'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">修改后 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="修改后">'.$arr['value'].'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                        $str .= '		</tr>';
                    }elseif(in_array($logTypeId,[3010104,3010204,3010304,3010404])){ //发布公告，帮助中心等
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">标题 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="标题">'.(isset($arr['n_title']) ? $arr['n_title'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">内容 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="内容">'.(isset($arr['n_content']) ? F::TextToHtml($arr['n_content']) : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">创建时间 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="创建时间">'.(isset($arr['n_createTime']) ? $arr['n_createTime'] : '-').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">发布者 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="发布者">'.(isset($arr['n_createId']) ? $arr['n_createId'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">是否置顶(0否1是) :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="是否置顶">'.(isset($arr['n_isTop']) ? $arr['n_isTop'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">主图 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="主图"><a href="'.(isset($arr['n_image']) ? $arr['n_image'] : '').'" target="_blank">'.(isset($arr['n_image']) ? $arr['n_image'] : '').'</a></td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">描述 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="描述">'.(isset($arr['n_meno']) ? $arr['n_meno'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">关键字 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="关键字">'.(isset($arr['n_keyword']) ? $arr['n_keyword'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                        $str .= '		</tr>';
                    }elseif(in_array($logTypeId,[3010102,3010202,3010302,3010402])){
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">新闻ID :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="关键字">'.(isset($arr['n_id']) ? $arr['n_id'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                        $str .= '		</tr>';
                    }elseif(in_array($logTypeId,[3010103,3010203,3010303,3010403])){
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">标题 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="标题">'.(isset($arr['n_title']) ? $arr['n_title'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">内容 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="内容">'.(isset($arr['n_content']) ? F::TextToHtml($arr['n_content']) : '-').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">更新时间 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="更新时间">'.(isset($arr['n_updateTime']) ? $arr['n_updateTime'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">修改者 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="修改者">'.(isset($arr['n_updateId']) ? $arr['n_updateId'] : '-').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">是否置顶(0否1是) :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="是否置顶">'.(isset($arr['n_isTop']) ? $arr['n_isTop'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">类型 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="类型">'.(isset($arr['n_type']) ? $arr['n_type'] : '').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">描述 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="描述">'.(isset($arr['n_meno']) ? $arr['n_meno'] : '-').'</td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">主图 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="主图"><a href="'.(isset($arr['n_image']) ? $arr['n_image'] : '').'" target="_blank">'.(isset($arr['n_image']) ? $arr['n_image'] : '-').'</a></td>';
                        $str .= '		</tr>';
                        $str .= '		<tr class="table-row">';
                        $str .= '		  <th class="hidden-sm col-md-2 borderd">备注 :	</th>';
                        $str .= '		  <td class="col-sm-12 col-md-10 borderd" data-title="备注">'.(isset($arr['memo']) ? $arr['memo'] : '-').'</td>';
                        $str .= '		</tr>';
                    }else{

                    }
                    break;
            }
        }
        $str .= '    </tbody>';
        $str .= '</table>';
        return $str;
    }

}