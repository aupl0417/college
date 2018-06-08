<?php
/**
 * Created by PhpStorm.
 * User: JoJoJing
 * Date: 2016/5/3
 * Time: 15:17
 */
class saveUser_json extends worker{
    function __construct($options) {
        $pk = isset($options['pk']) ? F::fmtNum($options['pk']) : "";
        if($pk == ""){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        $k = $pk-1;
        $db = new MySql();
        $sql = "select p_id from t_power_work where p_fid = '60110'";
        $powers = $db->getAll($sql);
        parent::__construct($options, [$powers[$k]['p_id']]);
    }

    function run() {
        $options = $this->options;
        $id = isset($options['id']) ? $options['id'] : "";
        $fields = isset($options['name']) ? $options['name'] : '';//表字段
		$value = isset($options['value']) ? $options['value'] : '';//值
		$pk = isset($options['pk']) ? F::fmtNum($options['pk']) : "";//pk
		$k = $pk-1;
		$val = "";
		if($id == "" || $fields == "" || $pk == ""){
            $this->show(message::getJsonMsgStruct('1002', '参数错误'));//参数错误
            exit;
        }

        $user = new user();
        $db = new MySql();
        $sql = "select u_type from t_user where u_id = '".$id."'";
        $type = $db->getField($sql);
        $power = "select p_id from t_power_work where p_fid = '60110'";
        $sql = "select u_nick from t_user where u_id = '".$id."'";
        $powers = $db->getAll($power);
        if(!$powers){
            $this->show(message::getJsonMsgStruct('1002','没有相关权限'));
            exit;
        }
        if(!$db->getField($sql)){
            $this->show(message::getJsonMsgStruct('1002','参数错误'));
            exit;
        }
        //查询t_user表的所有字段名
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 't_user' AND table_schema = '".DB_NAME."'";
        $fie = $db->getAll($sql);
        $fie = array_column($fie,'COLUMN_NAME');
        //判断所修改的值是否在t_user表
        if(in_array($fields,$fie)){ //在t_user表
            $sql = "select ".$fields." from t_user where u_id ='".$id."'"; //查询修改前的值
        }else{ //不在
            if($type == 0){ //查询个人用户修改前的值
                $sql = "select ".$fields." from t_user_person where u_id = '".$id."'";
            }else{ //查询企业用户修改前的值
                $sql = "select ".$fields." from t_user_company where u_id = '".$id."'";
            }
        }
        $res = $db->getField($sql);
        $arr['oldValue'] = isset($res) ? $res : "";

        $utType = 9;
        $arr['memo'] = '修改会员基本信息';

        if($fields == "u_nick"){ //修改会员ID
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','会员ID不能为空！'));
                exit;
            }
            if(!$user->uniqueUserInfo(1,$value,$id)){
                $this->show(message::getJsonMsgStruct('1002','会员ID格式不对或已存在！'));
                exit;
            }
            $utType = 6;
            $arr['memo'] = '修改会员ID';
        }

        if($fields == "u_name"){ //修改真实姓名
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','真实姓名为空！'));
                exit;
            }
            $utType = 14;
            $arr['memo'] = '修改真实姓名';
        }

        if($fields == "u_certNum"){ //修改身份证
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','身份证不能为空！'));
                exit;
            }
            if(!$user->uniqueUserInfo(4,$value,$id)){
                $this->show(message::getJsonMsgStruct('1002','该身份证号已存在！'));
                exit;
            }
            $utType = 15;
            $arr['memo'] = '修改会员身份证';
        }

        if($fields == "u_tel"){ //修改手机号
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','手机号不能为空！'));
                exit;
            }
            if(!F::isPhone($value)){
                $this->show(message::getJsonMsgStruct('1002','手机号格式错误！'));
                exit;
            }
            if(!$user->uniqueUserInfo(2,$value,$id)){
                $this->show(message::getJsonMsgStruct('1002','该手机号已存在！'));
                exit;
            }
            $utType = 12;
            $arr['memo'] = '修改手机号码';
        }

        if($fields == "u_email"){ //修改邮箱
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','邮箱不能为空！'));
                exit;
            }
            if(!F::isEmail($value)){
                $this->show(message::getJsonMsgStruct('1002','邮箱格式错误！'));
                exit;
            }
            if(!$user->uniqueUserInfo(8,$value,$id)){
                $this->show(message::getJsonMsgStruct('1002','该邮箱已存在！'));
                exit;
            }
            $utType = 13;
            $arr['memo'] = '修改用户邮箱操作';
        }

        if($fields == "u_qq"){ //修改QQ
            if(!F::isEmpty($value)){
                if(!F::fmtNum($value) || !F::isMaxLength($value,11)){
                    $this->show(message::getJsonMsgStruct('1002', 'qq错误'));
                    exit;
                }
            }
            $utType = 28;
            $arr['memo'] = '修改QQ号码';
        }

        if($fields == "u_address"){ //修改详细地址
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','请填写您的详细地址！'));
                exit;
            }
            $utType = 29;
            $arr['memo'] = '修改详细地址';
        }

        if($fields == "u_sex"){ //修改性别
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','请选择相应的性别！'));
                exit;
            }
            $sex = array('0' => '女','1' => '男',);
            $arr['oldValue'] = $sex[$arr['oldValue']];
            $val = $sex[$value];
            $utType = 30;
            $arr['memo'] = '修改性别';
        }

        if($fields == "u_country"){ //修改国家
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','国家为空！'));
                exit;
            }
            if(!F::fmtNum($value)){
                $this->show(message::getJsonMsgStruct('1002','国家格式错误!'));
                exit;
            }
            $country = $db->getAll("select coun_id as id,coun_name as `name` from t_country ORDER BY coun_id=37 DESC");
            $countryType = array_column($country,'name','id');
            $arr['oldValue'] = $countryType[$arr['oldValue']];
            $val = $countryType[$value];
            $utType = 31;
            $arr['memo'] = '修改国家';
        }

        if($fields == "u_companyName"){ //修改公司名称
            if(F::isEmpty($value) || !isset($value)){
                $this->show(message::getJsonMsgStruct('1002','公司名称不能为空！'));
                exit;
            }
            if(!$user->uniqueUserInfo(6,$value,$id)){
                $this->show(message::getJsonMsgStruct('1002','该公司名称已被占用！'));
                exit;
            }
            $utType = 22;
            $arr['memo'] = '修改公司名称';
        }

        if($fields == "u_comLegalName"){ //修改法人名字
            if(F::isEmpty($value) || !isset($value)){
                $this->show(message::getJsonMsgStruct('1002','法人不能为空！'));
                exit;
            }
            $utType = 16;
            $arr['memo'] = '修改法人名字!';
        }

        if($fields == "u_comLicenseCode"){
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','营业执照不能为空'));
                exit;
            }
            if(!$user->uniqueUserInfo(7,$value,$id)){
                $this->show(message::getJsonMsgStruct('1002','营业执照编号被占用'));
                exit;
            }
            $utType = 17;
            $arr['memo'] = '修改营业执照编号';
        }

        if($fields == "u_comOrgCode"){
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','组织机构代码证不能为空'));
                exit;
            }
            if(!$user->uniqueUserInfo(9,$value,$id)){
                $this->show(message::getJsonMsgStruct('1002','组织机构代码证被占用'));
                exit;
            }
            $utType = 18;
            $arr['memo'] = '修改组织机构代码证';
        }

        if($fields == "u_comTaxCode"){
            if(F::isEmpty($value)){
                $this->show(message::getJsonMsgStruct('1002','税务登记证不能为空!'));
                exit;
            }
            if(!$user->uniqueUserInfo(10,$value,$id)){
                $this->show(message::getJsonMsgStruct('1002','税务登记证已被占用！'));
                exit;
            }
            $utType = 23;
            $arr['memo'] = '修改税务登记证';
        }

        if($fields == "u_type"){ //会员身份转换
            $type = array('0' => '个人会员', '1' => '企业会员');
            $arr['oldValue'] = $type[$arr['oldValue']];
            $val = $type[$value];
            $utType = 7;
            $arr['memo'] = '会员身份转换';
        }

        if($fields == "u_createTime"){
            $utType = 25;
            $arr['memo'] = '修改注册时间';
        }

        if($fields == "u_birth"){
            $utType = 32;
            $arr['memo'] = '修改生日时间';
        }

        if($fields == "u_certType"){
            $utType = 33;
            $arr['memo'] = '';
        }

        if($fields == "u_isBranch"){
            $isBranch = array('0' => '否', '1' => '是');
            $arr['oldValue'] = $isBranch[$arr['oldValue']];
            $val = $isBranch[$value];
            $utType = 20;
            $arr['memo'] = '修改是否为分公司';
        }

        if($fields == "u_companyThree"){
            $isThree = array('0' => '否', '1' => '是');
            $arr['oldValue'] = $isThree[$arr['oldValue']];
            $val = $isThree[$value];
            $utType = 21;
            $arr['memo'] = '修改三证合一';
        }

        if($fields == "u_eduID"){
            $eduId = F::getAttrs(22);
            $arr['oldValue'] = $eduId[$arr['oldValue']];
            $val = $eduId[$value];
            $utType = 34;
            $arr['memo'] = '修改会员学历';
        }

        if($fields == "u_political"){
            $political = F::getAttrs(18);
            $arr['oldValue'] = $political[$arr['oldValue']];
            $val = $political[$value];
            $utType = 35;
            $arr['memo'] = '会员政治面貌修改';
        }

        if($fields == "u_career"){
            $utType = 36;
            $arr['memo'] = '会员职业修改';
        }

        if($fields == "u_native"){
            $utType = 37;
            $arr['memo'] = '籍贯修改';
        }

        if($fields == "u_postage"){
            $utType = 38;
            $arr['memo'] = '邮编修改';
        }

        if($fields == "u_otherTel"){
            $utType = 39;
            $arr['memo'] = '其他联系方式';
        }

        if($fields == "u_comMainIndustry"){
            $utType = 40;
            $arr['memo'] = '主营业务修改';
        }

        if($fields == "u_shopUrl"){
            $utType = 41;
            $arr['memo'] = '网址修改';
        }

        if($fields == "u_comAddress"){
            $utType = 42;
            $arr['memo'] = '经营地址修改';
        }

        try{
            $db->beginTRAN();
            //判断修改的字段是否在t_user表
            if(in_array($fields,$fie)){//在t_user
                //如果修改会员nick,则要同步修改pay_account表的nick
                if($fields == "u_nick"){
                    $res = apis::request('pay/api/updateAccountNick.json',['u_id' => $id, 'u_nick' => $value],true);
                    if($res['code'] != '1001'){
                        throw new Exception('-1');
                    }
                }

                $result = $db->update("t_user",[$fields => $value]," u_id = '".$id."'"); //更新t_user
                if(!$result){
                    throw new Exception('-1');
                }
            }else{ //不在t_user表
                if($type == 0){ //个人用户，修改t_user_person
                    $res = $db->update("t_user_person",[$fields => $value]," u_id = '".$id."'");
                }else{  //企业用户，修改t_user_company
                    $res = $db->update("t_user_company",[$fields => $value]," u_id = '".$id."'");
                }
                if(!$res){
                    throw new Exception('-1');
                }
            }

            $arr['field'] = $fields;
            $arr['value'] = $value.$val;
            log::writeLogMongo($powers[$k]['p_id'], 't_user', $id, $arr); //记录操作日志

            //历史操作
            $userTran = array(
                'ut_uid'	  => $id,
                'ut_type'	  => $utType,
                'ut_eid'	  => $_SESSION['userID'],
                'ut_ctime'	  => F::mytime(),
                'ut_oldValue' => $arr['oldValue'],
                'ut_newValue' => $value.$val,
                'ut_reason'   => $arr['memo'],
            );
            $res = $db->insert("t_user_tran", $userTran);
            if(!$res){
                throw new Exception('-1');
            }

            $db->commitTRAN();
            $this->show(message::getJsonMsgStruct('1001', "修改成功！"));

        }catch(Exception $e){
            $db->rollBackTRAN();
            $this->show(message::getJsonMsgStruct('1002','操作失败!'));
        }
    }
}