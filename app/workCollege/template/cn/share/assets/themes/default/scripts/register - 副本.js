var Register = function() {

    var handleRegister = function() {

        $('#form_register_member').handleForm(
			{
				rules: {
					'username': {						
						required: true,
						isusername: true,
						maxlength: 50,
						remote: {
							url: "/register/index.json?type=1",
							type: "get"
						}
					},
					'password': {
						required: true,
						minlength: 6,
						maxlength: 20
					},
					'repassword': {
						required: true,
						minlength: 6,
						maxlength: 20,
						equalTo: $("#register_password")
					},
					'mobile': {
						required: true,
						ismobile: true,
						remote: {
							url: "/register/index.json?type=2",
							type: "get"
						}
					},
					'smsCode': {
						required: true,
					},
					'referrer': {
						required: true,
						integer: true,
						remote: {
							url: "/register/index.json?type=3",
							type: "get"
						}
					},
					'ID': {
						required: true,
						isID: true,
						remote: {
							url: "/register/index.json?type=4",
							type: "get"
						}
					},
					'code': {
						required: true,
						minlength: 4,
						maxlength: 4,
						remote: {
							url: "/register/index.json?type=5",
							type: "get"
						}
					},
					'agreement': {
						required: true,
						minlength: 1						
					}
				},
				messages: {
					'username': {
						required: "请填写用户名",
						isusername: "用户名已被占用或者不符合规范<br/>8-50位字符，支持字母、数字、下划线组合。<br />必须以字母开头，字母或者数字结尾。",
						maxlength: "用户名已被占用或者不符合规范<br/>8-50位字符，支持字母、数字、下划线组合。<br />必须以字母开头，字母或者数字结尾。",
						success: "可以注册",
						remote: "用户名已被占用或者不符合规范<br/>8-50位字符，支持字母、数字、下划线组合。<br />必须以字母开头，字母或者数字结尾。"
					},
					'password': {
						required: "请填写密码",
						minlength: "6-20位字符，建议字母、数字及符号组合",
						maxlength: "6-20位字符，建议字母、数字及符号组合"
					},
					'repassword': {
						required: "请填写确认密码",
						minlength: "6-20位字符，建议字母、数字及符号组合",
						maxlength: "6-20位字符，建议字母、数字及符号组合",
						equalTo: "确认密码不一致"
					},
					'mobile': {
						required: "请填写手机",
						remote: "手机已被占用",
						success: "手机可以注册"
					},
					'smsCode': {
						required: "请填写手机验证码",
						
						success: "&nbsp;"
					},
					'referrer': {
						required: "推荐人错误，没有推荐人请填0",
						integer: "推荐人错误，没有推荐人请填0",
						remote: "推荐人错误，没有推荐人请填0",
						success: "推荐人正确"
					},
					'ID': {
						required: "请填写身份证",
						remote: "身份证已被占用",
						success: "身份证可以注册"
					},
					'code': {
						required: "验证码错误",
						minlength: "验证码错误",
						maxlength: "验证码错误",
						remote: "验证码错误",
						success: "&nbsp;"
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
				console.log('1');
				console.log(data);
				if(data.id == '2000'){
					bootbox.alert(data.msg, function() {						
						if(data.info.url){
							document.location.href = data.info.url;
						}	
					});
				}else{
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
    }
	/* 公司注册 */
    var handleRegisterCompany = function() {

        $('#form_register_company').handleForm(
			{
				rules: {
					'companyname': {						
						required: true,
						maxlength: 125,
						remote: {
							url: "/register/index.json?type=6",
							type: "get"
						}
					},
					'password': {
						required: true,
						minlength: 6,
						maxlength: 20
					},
					'repassword': {
						required: true,
						minlength: 6,
						maxlength: 20,
						equalTo: $("#company_password")
					},
					'mobile': {
						required: true,
						ismobile: true,
						remote: {
							url: "/register/index.json?type=2",
							type: "get"
						}
					},
					'referrer': {
						required: true,
						integer: true,
						remote: {
							url: "/register/index.json?type=3",
							type: "get"
						}
					},
					'companylicense': {
						required: true,
						islicense: true,
						remote: {
							url: "/register/index.json?type=7",
							type: "get"
						}
					},
					'code': {
						required: true,
						minlength: 4,
						maxlength: 4,
						remote: {
							url: "/register/index.json?type=5",
							type: "get"
						}
					},
					'agreement': {
						required: true,
						minlength: 1						
					}
				},
				messages: {
					'companyname': {
						required: "请填写公司名称",						
						maxlength: "公司名称不规范",
						success: "公司名称可以注册",
						remote: "公司名称已被占用"
					},
					'password': {
						required: "请填写密码",
						minlength: "6-20位字符，建议字母、数字及符号组合",
						maxlength: "6-20位字符，建议字母、数字及符号组合"
					},
					'repassword': {
						required: "请填写确认密码",
						minlength: "6-20位字符，建议字母、数字及符号组合",
						maxlength: "6-20位字符，建议字母、数字及符号组合",
						equalTo: "确认密码不一致"
					},
					'mobile': {
						required: "请填写手机",
						remote: "手机已被占用",
						success: "手机可以注册"
					},
					'referrer': {
						required: "推荐人错误，没有推荐人请填0",
						integer: "推荐人错误，没有推荐人请填0",
						remote: "推荐人错误，没有推荐人请填0",
						success: "推荐人正确"
					},
					'companylicense': {
						required: "请填写营业执照号",
						remote: "营业执照号已被占用",
						success: "营业执照号可以注册"
					},
					'code': {
						required: "验证码错误",
						minlength: "验证码错误",
						maxlength: "验证码错误",
						remote: "验证码错误",
						success: "&nbsp;"
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
				console.log('2');
				console.log(data);
				if(data.id == '2000'){
					bootbox.alert(data.msg, function() {						
						if(data.info.url){
							document.location.href = data.info.url;
						}	
					});
				}else{
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
    }

		
	//验证码
	
	var showPopover = function () {
		$(this).popover('show').prop('placeholder','请输入验证码,点击可刷新');
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
	function handleButtonCountdown(button){

		if (time == 0) {
			$(button).prop("disabled", false);			
			$(button).html("发送验证码");
			time = SMS_SENDINTERVAL;
		} else {
			$(button).prop("disabled", true);
			$(button).html("发送成功。重新发送(" + time + ")");
			time--;
			setTimeout(function(){
				handleButtonCountdown(button)
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
			//if(mobile.val().matchs('isMobile'))
			
			if($(mobile).valid())//手机号码验证通过
			{	
				Global.blockUI({zIndex: 99999});			
				$.ajax({
					'url': '/public/sms.json',
					'type': 'GET',
					'dataType': 'json',
					'data': {
						mobile: mobile.val()
					},
					success: function(res){						
						if(res.id == '1001'){
							Global.unblockUI();
							$("#row_smsCode", $(button).closest("form")).show();
							handleButtonCountdown(button);
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
		}
    };
}();