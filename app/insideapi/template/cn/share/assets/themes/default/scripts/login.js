var Login = function() {

    var handleLogin = function() {

        $('#form_login').handleForm(
			{
				rules: {
					'username': {						
						required: true,
						//isusername: true,
						maxlength: 50
					},
					'password': {
						required: true,
						minlength: 6,
						maxlength: 20
					},
					'code': {
						required: function(){
							return ($('#loginCode').css('display') == 'block');
						}/* ,
						minlength: 4,
						maxlength: 4 */
					}
				},
				messages: {
					'username': {
						required: "请填写用户名",
						isusername: "用户名为8-50位字符",
						maxlength: "用户名为8-50位字符",
						
					},
					'password': {
						required: "请填写密码",
						minlength: "密码为6-20位字符",
						maxlength: "密码为6-20位字符"
					},
					'code': {
						required: "请输入验证码图片中算术题的计算结果",
						minlength: "验证码错误",
						maxlength: "验证码错误"
					}					
				},
				closest: 'div.form-group',
				ass: {
					
				}
			},
			function(data, statusText){
				
				if(data.id == '2100'){
					msg = '<p>'+ data.msg +'</p>';
					for(e in data.info){
						//msg += '<p>' + e + ': ' + data.info[e] +'</p>';
						msg += '<p>点击确定进入控制台</p>';
					}
					bootbox.alert(msg, function() {						
						if(data.info.url){
							document.location.href = data.info.url;
						}	
					});
				}
				else if (data.id == '2109') {				
					bootbox.alert(data.msg, function(){
						document.location.href = data.info.url;
					});
				}
				else{
					$("#loginCode").show();
					Global.alert( {
					  "container": "#form_login",
					  "place": "prepend",
					  "type": "warning",
					  "message": data.msg,
					  "close": true,
					  "reset": true,
					  "focus": true,
					  "closeInSeconds": "0",
					  "icon": "warning"
					});	
					if (data.id == '2001') {
						$('input[name=code]').val('').focus();
					}
				}				
			}
		);

        $('#form_login input').keypress(function(e) {
            if (e.which == 13) {
                if ($('#form_login').validate().form()) {
                    $('#form_login').submit(); 
                }
                return false;
            }
        });
		
		//验证码
		
		var showPopover = function () {
			$(this).popover('show').prop('placeholder','请输入验证码图片中算术题的计算结果,点击可刷新');
			refreshCode();
		}
		, hidePopover = function () {
			$(this).popover('hide').prop('placeholder','点击显示验证码');
		}
		, refreshCode = function(){
			$('.popover #img_verification_code').attr('src','/validate/?type=code&code_len=4&font_size=16&width=100&height=30&font_color=&background=&refresh=1&time='+Math.random());						
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
    }

    var handleForgetPassword = function() {
        $('#form_forget').handleForm(
			{
				rules: {
					'uname': {						
						required: true
					},
					'phone': {						
						required: function () {
							var type = $("input[name='back_type']:checked").val();
							if(type == 0){
								return true;
							}else{
								return false;
							}
						},
						ismobile: true
					},
					'email': {
						required:function () {
							var type = $("input[name='back_type']:checked").val();
							if(type == 1){
								return true;
							}else{
								return false;
							}
						},
						email: true
					},
					'sms_code':{
						required: true,
						minlength: 6,
						maxlength: 6
					}
				},
				messages: {
					'uname': {
						required: "请填写用户名"
					},	
					'phone': {
						required: "请填写手机号码",
						ismobile: "手机号码格式不正确"
					},
					'email':{
						required: "请填写邮箱",
						email: "邮箱格式有误",
					},
					'sms_code':{
						required: "请输入验证码",
						minlength: "验证码为6位数字",
						maxlength: "验证码为6位数字",
					},
				},
				closest: 'div.form-group',
				ass: {
					
				}
			},
			function(data, statusText){
				
				if(data.id == '2100'){
					msg = '<p>'+ data.msg +'</p>';
					for(e in data.info){
						msg += '<p>' + e + ': ' + data.info[e] +'</p>';
					}
					bootbox.alert(msg, function() {						
						if(data.info.url){
							document.location.href = data.info.url;
						}	
					});
				}else{
					Global.alert( {
					  "container": "#form_login",
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
	

        $('.forget-form input').keypress(function(e) {
            if (e.which == 13) {
                if ($('.forget-form').validate().form()) {
                    //$('.forget-form').submit();
					
					
                }
                return false;
            }
        });



    }

    var handleNewPassword = function() {
        $('#form_pwd').handleForm(
			{
				rules: {
					'pwd': {						
						required: true,
						minlength: 8,
						maxlength: 20,
					},
					'pwd1': {						
						required: true,
						minlength: 8,
						maxlength: 20,
						equalTo: $("#pwd")
					},
				},
				messages: {
					'pwd': {
						required: "请填写密码",
						minlength: "8-20位字符，建议字母、数字及符号组合",
						maxlength: "8-20位字符，建议字母、数字及符号组合",
					},	
					'pwd1': {
						required: "请填写确认",
						minlength: "8-20位字符，建议字母、数字及符号组合",
						maxlength: "8-20位字符，建议字母、数字及符号组合",
						equalTo: "密码不一致"
					},	
				},
				closest: 'div.form-group',
				ass: {
					
				}
			},
			function(data, statusText){
				
				if(data.id == '2100'){
					msg = '<p>'+ data.msg +'</p>';
					for(e in data.info){
						msg += '<p>' + e + ': ' + data.info[e] +'</p>';
					}
					bootbox.alert(msg, function() {						
						if(data.info.url){
							document.location.href = data.info.url;
						}	
					});
				}else{
					Global.alert( {
					  "container": "#form_login",
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
	

        $('.forget-form input').keypress(function(e) {
            if (e.which == 13) {
                if ($('.forget-form').validate().form()) {
                    $('.forget-form').submit();
                }
                return false;
            }
        });
    }
    var handleToogleForm = function(){
        jQuery('#forget-password').click(function() {
            jQuery('.login-form').hide();
            jQuery('#form_pwd').hide();
            jQuery('#form_forget').show();
        });

        jQuery('#back-btn').click(function() {
			jQuery('.login-form').show();
	        jQuery('#form_pwd').hide();
	        jQuery('#form_forget').hide();
        });
		jQuery('#back-login').click(function() {
			jQuery('.login-form').show();
	        jQuery('#form_pwd').hide();
	        jQuery('#form_forget').hide();
        });    	
    }
    return {
        //main function to initiate the module
        init: function() {
            handleLogin();
            handleForgetPassword();
            handleNewPassword();
            handleToogleForm();
        }
    };
}();


