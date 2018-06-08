var Reset = function() {

    var handleReset = function() {

        $('#form_reset').handleForm(
			{
				rules: {
					'newusername': {						
						required: true,
						isusername: true,
						maxlength: 50
					},
					'password': {
						required: true/* ,
						minlength: 6,
						maxlength: 20 */
					},
					'ID': {/*
						required: true ,
						isID: true */
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
					'newusername': {
						required: "请填写用户名",
						isusername: "用户名由中英文、数字或下划线组合而成，<br/>不能以数字或下划线开头且不能以下划线结尾，长度为6-50个字符",
						maxlength: "用户名超过50位",
					},
					'password': {
						required: "请填写密码",
						minlength: "6-20位字符，建议字母、数字及符号组合",
						maxlength: "6-20位字符，建议字母、数字及符号组合"
					},
					'ID': {
						required: "请填写身份证",
						remote: "身份证已被占用"
					},
					'code': {
						required: "请输入验证码图片中算术题的计算结果",
						minlength: "验证码错误",
						maxlength: "验证码错误"
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
				if(data.id == '1001'){
					data.msg = data.info || data.msg;
					bootbox.alert(data.msg, function() {						
						document.location.href = '/';	
					});
				}else{					
					data.msg = data.info || data.msg;
					Global.alert( {
					  "container": "#form_reset",
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
            handleReset();         
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
						mobile: mobile.val(),
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
		}
    };
}();