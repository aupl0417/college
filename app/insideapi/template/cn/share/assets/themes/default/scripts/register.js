var Register = function() {

    var handleRegister = function() {

        $('#form_register_member').handleForm(
			{
				rules: {
					'username': {						
						required: true,
						minlength: 6,
						maxlength: 20,
						isusername: true
					},
					'password': {
						required: true,
						minlength: 8,
						maxlength: 20
					},
					'repassword': {
						required: true,
						minlength: 8,
						maxlength: 20,
						equalTo: $("#register_password")
					},
					'mobile': {
						required: function(){
							return $("select[name='country'] > option:selected").val() == '37';
						},
						ismobile: true
					},
					'sendTime': {
						required: function(){
							return $("select[name='country'] > option:selected").val() == '37';
						},
					},
					'smsCode': {
						required: true
					},
					'referrer': {
						//required: true
					},
					'ID': {
						required: function(){
							return $("select[name='country'] > option:selected").val() == '37';
						},
						isID: true
					},
					'n_ID': {
						required: function(){
							return $("select[name='country'] > option:selected").val() != '37';
						},
					},
					'code': {
						required: true
					},
					'Email': {
						required: function(){
							return $("select[name='country'] > option:selected").val() != '37';
						},
						email: true
					},
					'agreement': {
						required: true,
						minlength: 1						
					}
				},
				messages: {
					'username': {
						required: "请填写用户名",
						isusername: "用户名由中英文、数字或下划线组合而成，<br/>不能以数字或下划线开头且不能以下划线结尾，长度为6-20个字符",
						minlength: "用户名不能少于6位",
						maxlength: "用户名不能超过20位",
					},
					'password': {
						required: "请填写密码",
						minlength: "8-20位字符，建议字母、数字及符号组合",
						maxlength: "8-20位字符，建议字母、数字及符号组合"
					},
					'repassword': {
						required: "请填写确认密码",
						minlength: "8-20位字符，建议字母、数字及符号组合",
						maxlength: "8-20位字符，建议字母、数字及符号组合",
						equalTo: "确认密码不一致"
					},
					'mobile': {
						required: "请填写手机"
					},
					'sendTime': {
						required: "请点击发送发送验证码"
					},
					'smsCode': {
						required: "请填写手机验证码"
					},
					'referrer': {
						integer: "推荐码错误"
					},
					'ID': {
						required: "请填写身份证",
						remote: "身份证已被占用"
					},
					'n_ID': {
						required: "请填写身份证",
					},
					'code': {
						required: "请输入验证码图片中算术题的计算结果"
					},
					'Email': {
						required: "请输入您的邮箱",
						email: "请输入正确的邮箱"
					},
					'agreement': {
						required: "请阅读并同意《大唐天下商务系统平台注册》",
						minlength: "请阅读并同意《大唐天下商务系统平台注册》"
					}					
				},
				closest: 'div.form-group',
				ass: {
					
				}
			},
			function(data, statusText){
				if(data.id == '2000'){
					bootbox.alert(data.msg, function() {						
						if(data.info.url){
							document.location.href = data.info.url;
						}	
					});
				}else{
					var el = $("input[name='"+data.info+"']", $('#form_register_member'));
					el.focus();
					switch(data.info){
						case 'mobile':
							el.prop('readonly', false).focus();
							break;
						
					}
					Global.alert( {
					  "container": "#form_register_member",
					  "place": "prepend",
					  "type": "warning",
					  "message": data.msg,
					  "close": true,
					  "reset": true,
					  "focus": true,
					  "closeInSeconds": "0",
					  "icon": "warning"
					});			
				}				
			}
		);

        $('#form_register_member input').keypress(function(e) {
            if (e.which == 13) {
                if ($('#form_register_member').validate().form()) {
                    $('#form_register_member').submit(); 
                }
                return false;
            }
        });
		
		$('#register_mobile').on('blur',function(){
			var mobile = $(this).val();
			
			if(mobile.matchs('isMobile')){
				$.ajax({
					'url': '/public/setmobile.json',
					'type': 'POST',
					'dataType': 'json',
					'data': {
						mobile: mobile
					},
					success: function(res){}
					
				});	
			}
		});
    }
	/* 公司注册 */
    var handleRegisterCompany = function() {

        $('#form_register_company').handleForm(
			{
				rules: {
					'username': {						
						required: true,
						minlength: 6,
						maxlength: 20,
						isusername: true
					},
					'companyname': {						
						required: true,
						//isgfw:true,
						maxlength: 125
					},
					'password': {
						required: true,
						minlength: 8,
						maxlength: 20
					},
					'repassword': {
						required: true,
						minlength: 8,
						maxlength: 20,
						equalTo: $("#company_password")
					},
					'mobile': {
						required: true,
						ismobile: true
					},
					'sendTime': {
						required: true
					},
					'smsCode': {
						required: true
					},
					'referrer': {
						//required: true
					},
					'companylicense': {
						required: true,
						islicense: true
					},
					'code': {
						required: true
					},
					'agreement': {
						required: true,
						minlength: 1						
					}
				},
				messages: {
					'username': {
						required: "请填写用户名",
						isusername: "用户名已被占用或者不符合规范<br/>6-20位字符，支持字母、数字、下划线组合。<br />必须以字母开头，字母或者数字结尾。",
						minlength: "用户名不能少于6位",
						maxlength: "用户名不能超过20位",
					},
					'companyname': {
						required: "请填写公司名称",	
						isgfw:"公司名称不规范",
						maxlength: "公司名称不规范"
					},
					'password': {
						required: "请填写密码",
						minlength: "8-20位字符，建议字母、数字及符号组合",
						maxlength: "-20位字符，建议字母、数字及符号组合"
					},
					'repassword': {
						required: "请填写确认密码",
						minlength: "8-20位字符，建议字母、数字及符号组合",
						maxlength: "8-20位字符，建议字母、数字及符号组合",
						equalTo: "确认密码不一致"
					},
					'mobile': {
						required: "请填写手机"
					},
					'smsCode': {
						required: "请填写手机验证码"
					},
					'sendTime': {
						required: "请点击发送发送验证码"
					},
					'referrer': {
						integer: "推荐人错误"
					},
					'companylicense': {
						required: "请填写营业执照号码或统一社会信用代码号"
					},
					'code': {
						required: "请输入验证码图片中算术题的计算结果"
					},
					'agreement': {
						required: "请阅读并同意《大唐天下商务系统平台注册》",
						minlength: "请阅读并同意《大唐天下商务系统平台注册》"
					}					
				},
				closest: 'div.form-group',
				ass: {
					
				}
			},
			function(data, statusText){
				if(data.id == '2000'){
					bootbox.alert(data.msg, function() {						
						if(data.info.url){
							document.location.href = data.info.url;
						}	
					});
				}else{
					var el = $("input[name='"+data.info+"']", $('#form_register_member'));
					el.focus();
					switch(data.info){
						case 'mobile':
							el.prop('readonly', false).focus();
							break;
						
					}
					Global.alert( {
					  "container": "#form_register_company",
					  "place": "prepend",
					  "type": "warning",
					  "message": data.msg,
					  "close": true,
					  "reset": true,
					  "focus": true,
					  "closeInSeconds": "0",
					  "icon": "warning"
					});			
				}				
			}
		);

        $('#form_register_company input').keypress(function(e) {
            if (e.which == 13) {
                if ($('#form_register_company').validate().form()) {
                    $('#form_register_company').submit(); 
                }
                return false;
            }
        });
		$('#company_mobile').on('blur',function(){
			var mobile = $(this).val();
			
			if(mobile.matchs('isMobile')){
				$.ajax({
					'url': '/public/setmobile.json',
					'type': 'POST',
					'dataType': 'json',
					'data': {
						mobile: mobile
					},
					success: function(res){}
					
				});	
			}
		});
    }

		
	//验证码
	
	var showPopover = function () {
		$(this).popover('show').prop('placeholder','请输入验证码图片中算术题的计算结果,点击可刷新');
		refreshCode();
	}
	, hidePopover = function () {
		$(this).popover('hide').prop('placeholder','点击显示验证码');
	}
	, refreshCode = function(){
		$('.popover #img_verification_code').attr('src','/validate/?type=checkcode&code_len=4&font_size=16&width=100&height=30&font_color=&background=&refresh=1&time='+Math.random());						
	};
	
	$('input[name=code]').popover({
		html : true, 
		placement : 'bottom',
		content: function() {
			return $('#html_verification_code').html();
		},	  //$('<img id="img_verification_code" alt="" src="">'),
		trigger: 'manual'
	})
	.focus(showPopover)
	.blur(hidePopover)
	.click(refreshCode);
	
	var time = SMS_SENDINTERVAL;
	function handleButtonCountdown(button, mobile){

		if (time == 0) {
			$(button).prop("disabled", false);			
			$(button).html("重新发送");
			$(mobile).prop('readonly', false);
			time = SMS_SENDINTERVAL;
		} else {
			$(button).prop("disabled", true);
			$(button).html("发送成功。重新发送(" + time + ")");
			time--;
			setTimeout(function(){
				handleButtonCountdown(button, mobile)
			}, 1000);
		}
		
	}
	
    return {       
        init: function() {
            handleRegister();  
            handleRegisterCompany();          
        },
		sendSms: function(button){
			
			var mobile = $("input[name='mobile']", $(button).closest("form"));
			var code = $("input[name='code']", $(button).closest("form"));
		
			if(!$("input", $(button).closest("div.form-group").prevAll("div.form-group")).valid()){//发送短信之前必须先填写的字段
				Global.alert( {
				  "container": $(button).closest('form'),
				  "place": "prepend",
				  "type": "warning",
				  "message": '请填写注册信息',
				  "close": true,
				  "reset": true,
				  "focus": true,
				  "closeInSeconds": "0",
				  "icon": "warning"
				});	
				return false;
			};
			
			//$(button).closest("form").valid();
			//return false;
			//if(mobile.val().matchs('isMobile'))
			
			if($(mobile).valid())//手机号码验证通过
			{	
				Global.blockUI({zIndex: 99999});			
				$.ajax({
					'url': '/public/sms.json',
					'type': 'GET',
					'dataType': 'json',
					'data': {
						mobile: mobile.val(),
						code:code.val(),
						chkMobile: 1
					},
					success: function(res){		
						Global.unblockUI();				
						if(res.id == '1001'){
							$("#row_smsCode", $(button).closest("form")).show();
							$("input[name='sendTime']", $(button).closest("form")).val('1');
							handleButtonCountdown(button, mobile);
							$(mobile).prop('readonly', true);
						}else{
							Global.alert( {
							  "container": $(button).closest('form'),
							  "place": "prepend",
							  "type": "warning",
							  "message": res.info,
							  "close": true,
							  "reset": true,
							  "focus": true,
							  "closeInSeconds": "0",
							  "icon": "warning"
							});								
						}
					}
					
				});
			}else{
				Global.alert( {
				  "container": $(button).closest('form'),
				  "place": "prepend",
				  "type": "warning",
				  "message": '请输入正确的手机号码',
				  "close": true,
				  "reset": true,
				  "focus": true,
				  "closeInSeconds": "0",
				  "icon": "warning"
				});				
			}
		},
		sendSms1: function(button){
			
			var email = $("input[name='Email']", $(button).closest("form"));
			var code = $("input[name='code']", $(button).closest("form"));
		
			if(!$("input", $(button).closest("div.form-group").prevAll("div.form-group")).valid()){//发送短信之前必须先填写的字段
				Global.alert( {
				  "container": $(button).closest('form'),
				  "place": "prepend",
				  "type": "warning",
				  "message": '请填写注册信息',
				  "close": true,
				  "reset": true,
				  "focus": true,
				  "closeInSeconds": "0",
				  "icon": "warning"
				});
				return false;
			};
			
			//$(button).closest("form").valid();
			//return false;
			//if(mobile.val().matchs('isMobile'))
			
			if($(email).valid())//手机号码验证通过
			{	
				Global.blockUI({zIndex: 99999});			
				$.ajax({
					'url': '/public/email.json',
					'type': 'GET',
					'dataType': 'json',
					'data': {
						email: email.val(),
						code:code.val(),
					},
					success: function(res){		
						Global.unblockUI();				
						if(res.id == '1001'){
							$("#row_smsCode", $(button).closest("form")).show();
							$("input[name='sendTime']", $(button).closest("form")).val('1');
							handleButtonCountdown(button, email);
							$(email).prop('readonly', true);
						}else{
							Global.alert( {
							  "container": $(button).closest('form'),
							  "place": "prepend",
							  "type": "warning",
							  "message": res.info,
							  "close": true,
							  "reset": true,
							  "focus": true,
							  "closeInSeconds": "0",
							  "icon": "warning"
							});								
						}
					}
					
				});
			}else{
				Global.alert( {
				  "container": $(button).closest('form'),
				  "place": "prepend",
				  "type": "warning",
				  "message": '请您输入正确的邮箱',
				  "close": true,
				  "reset": true,
				  "focus": true,
				  "closeInSeconds": "0",
				  "icon": "warning"
				});				
			}
		}
    };
}();