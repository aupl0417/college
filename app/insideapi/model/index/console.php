<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/25
 * Time: 11:46
 */
class console extends guest{

    function __construct($options = array(), $power = array()) {
        //$this->openCache();
        parent::__construct($options, $power);
    }

    function run(){
        $this->foot = F::readFile(APPROOT. '/template/cn/index/share/footapilist.html');

        //判断是否已登录和申请状态  （申请状态dp_status：）
        $db = new MySql();
        @$sql = "select * from t_develop_partner WHERE dp_uid = '".$_SESSION['userID']."'";
        $result = $db->getRow($sql);
        if($result){
            if($result['dp_status'] == 1){
                $_SESSION['dp_id'] = $result['dp_id'];
                header('location:http://'.INSIDEAPI.'/index/appManager');
            }else if($result['dp_status'] == 0){
                $data['check'] = '<div class="portlet-body" style="margin: 50px 330px 0 0">
						<div class="alert alert-info"> <i class="fa fa-lg fa fa-exclamation-circle"></i>&nbsp;您已经向本平台提交了开发者申请，我们会尽快处理您的申请</div>
						<br />
						<br />
						<br />
						<h4 class="text-center">您已提交了申请，请耐心等候审核！</h4>
					</div>';
            }else if($result['dp_status'] == 2 || $result['dp_status'] == 3){
                $data['check'] = '<div class="portlet-body" style="margin: 50px 330px 0 0">
						<div class="alert alert-info"> <i class="fa fa-lg fa fa-exclamation-circle"></i>&nbsp;您的账号在本平台出现异常，现已禁止登录本平台，如有疑问请联系我们！</div>
						<br />
						<br />
						<br />
						<h4 class="text-center">您的账号不允许在本平台登录，请联系管理员！</h4>
					</div>';
            }else{
                $data['check'] = '<div class="portlet-body" style="margin: 50px 330px 0 0">
						<div class="alert alert-info"> <i class="fa fa-lg fa fa-exclamation-circle"></i>&nbsp;您的申请出现问题，已被管理员拒绝申请</div>
						<br />
						<br />
						<br />
						<replace value="dp_mome">
							<h4 class="text-center">拒绝原因：'.$result['dp_mome'].'</h4>
						</replace>
						<h4 class="text-center block"><a href="/index/beDeveloper" class="btn blue btn-lg" target="_blank">成为开发者</a></h4>
					</div>';
            }
        }else if(!isset($_SESSION['userID'])){
            $data['check'] = '<div class="portlet-body" style="margin: 50px 330px 0 0">
						<div class="alert alert-info"><i class="fa fa-lg fa-exclamation-circle"></i>&nbsp;本开放平台的账号和大唐商城账号一样，可以使用使用大唐商城账号登录本平台</div>
						<br />
						<br />
						<br />
						<h4 class="text-center">您还没有登录，请先登录！</h4>
					</div>';
        }else{
            $data['check'] = '<div class="portlet-body" style="margin: 50px 330px 0 0">
						<div class="alert alert-info"> <i class="fa fa-lg fa fa-exclamation-circle"></i>&nbsp;您还不是开发者，请先申请成为开发者，以便平台给您提供更好的服务。<!--a href="" class="font-blue">请点击这里 </a--></div>
						<br />
						<br />
						<br />
						<h4 class="text-center">你还没有成为开发者，您可以</h4>
						<h4 class="text-center block"><a href="/index/beDeveloper" class="btn blue btn-lg">申请成为开发者</a></h4>
					</div>';
        }
        $this->setReplaceData($data);
        $this->setTempAndData('console/console');
        $this->show();
    }
}