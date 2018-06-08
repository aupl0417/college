//表单的验证-提交-回调
(function ($) {
	$.handleForm = function (el, options, callback) {
		var form = this;		
		form.$el = $(el);
		
		var isForm = ($(el).prop('nodeName') == 'FORM');
		if(typeof options === 'function'){
			callback = options;
			options = {};
		}
		form.callback = null || callback;
		
		form.el = el;
		
		form.defaults = {
			rt : false,//实时验证,
			errorElement: 'span', //默认用于显示错误信息的标签 div span p
			errorClass: 'help-block help-block-error', // 默认错误信息的样式
			focusInvalid: false, // 第一个错误的input获取焦点
			ignore: "", // 隐藏域,
			closest : '.form-group', //用于寻找parent的标识			
			parentError: 'has-error',//parent高亮错误
			parentSuccess: 'has-success',//parent高亮成功
			ass: {//表单提交成功后的操作 none:不进行任何操作;reset:重置表单;alert:弹窗();closeWindow:关闭当前窗口;closeModal:关闭弹层;href:链接
				//'alert':'提交成功!'
				'none': true
			},
			pulsate: false,
			type: "POST", // 提交表单的方式,
			dataType: "JSON",//返回数据的格式
			beforeSubmit: null,
			confirm: null,
			isSubmit: null
			
		};
		form.options = $.extend({}, form.defaults, options);
		
		var $formSuccess = function (data, statusText, xhr, $form) {
			//console.log(data);
			if (data) {
				if(data.id == '0004'){//没有登录或者登录超时
					bootbox.alert('您还没有登录或者登录超时!', function(){
						document.location.href = data.info.url;
					});
					return false;
				}
				$().cookie && $.cookie("refersh_time", 1);
				setTimeout(function(){
					if(typeof layer != 'undefined'){
						layer.closeAll();
					}else{
						Global.unblockUI();
					}
					//
					
					//
					/* 提交成功后的常用操作 */
					if(!form.options.ass.none){
						form.options.ass.alert && bootbox.alert(form.options.ass.alert, function(){
							form.options.ass.href && (document.location.href = form.options.ass.href);
						});
						
						form.options.ass.reset && $(form.el)[0].reset();
						
						form.options.ass.closeWindow && window.close();
						
						//form.options.ass.href && (form.options.ass.alert ? alert(form.options.ass.alert) : {}, (document.location.href = form.options.ass.href));
						!form.options.ass.alert && (form.options.ass.href && (document.location.href = form.options.ass.href))
					}
					if(data){
						switch(data.id){
							case '0001':
								
								break;
							
							case '0002':
								
								break;
							default:
								
								break;
						}
					}
					/* 自定义 */
					//console.log(form.callback);
					form.callback && form.callback(data, statusText, xhr, $form);
				},1000);
				
			} else {
				
			}
		}	
		
		if(!isForm){//不是表单,直接提交
		
			
			form.$el.on('click', "[type='submit']", function(){
				var non_form_options = $.extend(true, {
					url: form.$el.data('action'),
					success: $formSuccess,
					type: form.options.type,
					data: $(form.el).find(':input').serializeObject()
				}, options);				
				$.ajax(non_form_options);
			});
			return false;
		}
		//form

		jQuery.validator.addMethod("sum", function (value, element, params) {
				var sumOfVals = 0;
				var parent = $(element).closest(".groupSum");
				$(parent).find("input.sum").each(function () {
					sumOfVals = sumOfVals.add($(this).val() - 0);
				});
				if (sumOfVals == params) return true;
				return false;
			},
			"所有项目之和必须为 {0}"
		);		
		
		//添加金额验证
 		jQuery.validator.addMethod("money", function(value, element) {
				return this.optional(element) || /^\d+(\.\d{1,2})?$/i.test(value);
			}, 
		"金额格式错误");              
		//添加时间验证规则
/* 		jQuery.validator.addMethod("datetime", function(value, element) {
				return this.optional(element) || /^ylh\d{5}$/i.test(value);
			}, 
		"员工号格式错误"); */
		//增加编辑器验证规则
		jQuery.validator.addMethod('editorcontent', function () {
			return editorcontent.getContent();
		});

		//增加编辑器验证规则
		if(typeof ueditors !== 'undefined' ){
			for(e in ueditors){
				jQuery.validator.addMethod(e, function () {
					return ueditors[e].getContent();
				});				
			}
		}
		//不等于验证
		jQuery.validator.addMethod("notEqual", function(value, element, param) {
		  return this.optional(element) || value != param;
		}, "");
		
		//添加账号验证规则
		jQuery.validator.addMethod("isusername", function(value, element) {
				return this.optional(element) || (/^[a-zA-Z\u4e00-\u9fa5]{1}[\u2027·a-zA-Z0-9\_\u4e00-\u9fa5]{4,18}[a-zA-Z0-9\u4e00-\u9fa5]{1}$/.test(value) && !/^(大唐天下|dttx|客服|管理员|系统管理员)/i.test(value) && !/^(tel|pos|wifi)1[3|4|5|7|8]\d{9}$/i.test(value) && !/(全返|赠送|大唐天下|dttx|大唐|dt|大堂|云联惠|云联|yunlianhui|yunlian|唐人街|云连惠|云连会|云支付|云加速|云数据|芸联惠|芸连惠|芸连会|芸联会|云联汇|云连汇|芸联汇|芸连汇|匀连惠|匀联惠|匀联汇|云联惠|老战士|云转回|匀加速|零购|老战士|云回转|匀加速|零购|云支付|成谋商城|脉单|众智云|麦点|秀吧|一点公益|商城联盟)/i.test(value));
			}, 
		"用户名格式错误");
                
		//添加员工号验证规则
		jQuery.validator.addMethod("isworkname", function(value, element) {
				return this.optional(element) || /^dttx\d{5}$/i.test(value);
			}, 
		"员工号格式错误");
		
		//添加手机验证规则
		jQuery.validator.addMethod("ismobile", function(value, element) {
				return this.optional(element) || /^0?(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[0-9])[0-9]{8}$/.test(value);
			}, 
		"手机号码格式错误");
		
		//添加身份证验证规则
		jQuery.validator.addMethod("isID", function(value, element) {
				return this.optional(element) || value.isIdCardNo();
			}, 
		"身份证号码格式错误");
		
		//添加香港身份证验证规则
		jQuery.validator.addMethod("isHKID", function(value, element) {
				return this.optional(element) || value.isHKIdCardNo();
			}, 
		"香港身份证号码格式错误");
		
		//添加营业执照验证规则
		jQuery.validator.addMethod("islicense", function(value, element) {
				return this.optional(element) || /^[a-z\d]{1,20}$/i.test(value);
			}, 
		"营业执照格式错误");
		
		//添加组织机构代码证号码验证规则
		jQuery.validator.addMethod("isorgcode", function(value, element) {
				return this.optional(element) || /^[0-9A-Z]{8}\-[0-9A-Z]{1}$/.test(value);
			}, 
		"组织机构代码证号码格式错误");
		
		//添加安全密码验证规则
		jQuery.validator.addMethod("issafe", function(value, element) {
				return this.optional(element) || /^\d{6}$/.test(value);
			}, 
		"安全密码格式错误");
		
		//添加敏感字验证
		jQuery.validator.addMethod("isgfw", function(value, element) {
				return this.optional(element) || /^(?!.*((操你)|(她妈)|(它妈)|(他妈)|(你妈)|(妈逼)|(妈B)|(fuck)|(去死)|(贱人)|(妈B)|(叼)|(擦)|(戳)|(CAO)|(TMD)|(尼玛)|(垃圾)|(屁)|(龌龊)|(SB)|(煞笔)|(傻B)|(祖宗)|(白痴)|(吃屎)|(香港占中)|(香港中环)|(香港民主)|(香港大学生)|(客服)|(云(\s|\*|\-|\||\@|\#|\&|\%|\.)*联))).*$/i.test(value);
			}, 
		"请勿发表不恰当言论");
		//添加中文验证
		jQuery.validator.addMethod("ischinese", function(value, element) {
			return this.optional(element) || (new RegExp(/^[\u4E00-\u9FA5]+$/)).test(value);
		}, "非中文格式");

		//添加英文验证
		jQuery.validator.addMethod("isenglishName",function(value, element){
			if(value){
				return  /^[a-zA-Z.\.]{2,20}$/.test(value);
			}else{
				return true;
			}
		},"非英文名格式");
		//真实姓名
		jQuery.validator.addMethod("isCnName", function(value, element) {
			return /^([\u4e00-\u9fa5]+[·]?){2,20}$/.test(value);
		},'真实姓名格式错误');
		//校验登陆用户名不能有特殊字符
		jQuery.validator.addMethod("login", function(value, element) {
			return !/\s|\'|\"|\#|\-{2}|\+|\;|\*|\//.test(value);
		},'请输入用户名/手机号码/Email');
		//检验是不是100的整倍数
		jQuery.validator.addMethod("divide100", function(value, element) {
			return value % 100 == 0;
		},'必须是100的整倍');

				
		
		//表单验证开始
		form.$el.validate({
			onclick : form.options.rt,//false,//实时验证
			errorElement: form.options.errorElement, //默认用于显示错误信息的标签 div span p
			errorClass: form.options.errorClass, // 默认错误信息的样式
			focusInvalid: form.options.focusInvalid, // 第一个错误的input获取焦点
			ignore: form.options.ignore, // 隐藏域
			rules: form.options.rules,

			messages: form.options.messages,

			errorPlacement: function (error, element) { // 处理错误信息
				el = $(element).prop('name');
								
				el.indexOf('[') == -1 && $('#'+ el +'-success').hide();	//先屏蔽成功信息		
				/* if (element.prev("i.icon-check").size() > 0) {
					var icon = element.prev("i.icon-check").prop('class').match(/fa\-\S+\b/g, '');
					console.log(icon);
					element.prev("i.icon-check").removeClass('fa-*').addClass('fa-times');
				} else  */
				
				if (element.parent(".input-icon").size() > 0) {
					if($('#'+ el +'-error').length > 0)$('#'+ el +'-error').remove();//如果已存在错误信息,先删除
					error.insertAfter(element.parent(".input-icon"));
				} else if (element.parent(".input-group").size() > 0) {
					error.insertAfter(element.parent(".input-group"));
				} else if (element.attr("data-error-container")) {
					
					error.appendTo($('#'+element.attr("data-error-container")));
				} else if (element.parents('.radio-list').size() > 0) { 
					error.appendTo(element.parents('.radio-list').attr("data-error-container"));
				} else if (element.parents('.radio-inline').size() > 0) { 
					error.appendTo(element.parents('.radio-inline').attr("data-error-container"));
				} else if (element.parents('.checkbox-list').size() > 0) {
					error.appendTo(element.parents('.checkbox-list').attr("data-error-container"));
				} else if (element.parents('.checkbox-inline').size() > 0) { 
					error.appendTo(element.parents('.checkbox-inline').attr("data-error-container"));
				} else if (element.next('#'+error[0].id).size() > 0) { 
					element.next('#'+error[0].id).replaceWith(error);
				} else {
					//console.log($('#'+ el +'-error').length);
					//console.log(element);
					if(element.prop('type') != 'hidden'){
						if($('#'+ el +'-error').length > 0)$('#'+ el +'-error').remove();//如果已存在错误信息,先删除
					}
					error.insertAfter(element); 
				}
			},

			invalidHandler: function (event, validator) {
				//console.log(event, validator);
				if(typeof layer != 'undefined' && typeof validator.errorList[0].message == 'string'){
					layer.msg(validator.errorList[0].message);
				}
				Global.scrollTo($(validator.errorList[0].element));
				//console.log(validator.errorList);
			},

			highlight: function (element, label) { // 高亮错误
				el = $(element).prop('name');
				el.indexOf('[') == -1 && $('#'+ el +'-success', form.$el).hide();	//先屏蔽成功信息	
				//
				$(element).closest(form.options.closest).addClass(form.options.parentError); 
				
				form.options.pulsate && $(element).pulsate({color: "#bf1c56", repeat: false});	
/* 				if($('#'+ el +'-error').length > 0){
					$('#'+ el +'-error').show();
					return false;
				}; */
			},

			unhighlight: function (element) { // 通过验证后移除高亮错误
				$(element).closest(form.options.closest).removeClass(form.options.parentError); 
				var el = $(element).prop('name');
				el.indexOf('[') == -1 && $('#'+ el +'-error').remove();				
			},

			success: function (label, element) {//通过验证后添加样式	
				
				label.closest(form.options.closest).addClass(form.options.parentSuccess);
				var el = $(element).prop('name');
				el.indexOf('[') == -1 && $('#'+ el +'-error').remove();
				if(el.indexOf('[') == -1){
					var elSuccess = $('#'+ el +'-success', form.$el);				
				}else{
					var elSuccess = {length:0};
				}
				if(elSuccess.length == 1){
					elSuccess
 						.html(function(){
							return (form.options.messages[el].success) ? '<i class="fa fa-check font-success"></i>' + form.options.messages[el].success : $(this).html();
						})
						.show();
				}else{
					//console.log(form.options.messages[el]);
					
					if(form.options.messages[el] && form.options.messages[el].success){
						elSuccess = $('<span/>')
										.prop('id', el +'-success')
										.addClass('help-block')
										.html('<i class="fa fa-check font-success"></i>' + form.options.messages[el].success);
						$(element).after(elSuccess);
					}
				}
			},

			submitHandler: function (subForm, e) {//验证通过,提交表单				
                //e.preventDefault();
				
				if(form.options.confirm){					
 					if(typeof form.options.confirm == 'string'){
						var msg = form.options.confirm;
					}else{
						var msg = form.options.confirm();
						msg = msg || '确定提交吗?';
					}
					bootbox.confirm(msg, function(res){										
						if(res){
							submitForm(subForm, form, e);										
						}else{							
						};
					}); 
				}else{					
					submitForm(subForm, form);
				}
			}

		});
		
		var submitForm = function(subForm, form, e){
			//console.log(subForm, form, e);//
			if($("input[name='_ajax']", $(subForm)).length == 0){
				$(subForm).append('<input name="_ajax" type="hidden" value="1" />');
			}
			
			var sub = (typeof form.options.isSubmit === 'function') ? form.options.isSubmit() : form.options.isSubmit;
			if(sub){
				e.submit();
				return false;
			}else{
				//console.log($formSuccess);
				try{
					$(subForm).ajaxSubmit({
						url : $(subForm).attr('action'), //多个地址可选怎么解决?
						type : form.options.type,
						dataType : form.options.dataType,
						beforeSubmit : function (arr, $form, options) {	
							if(form.options.beforeSubmit){ 
								form.options.beforeSubmit();
							}
							if(typeof layer != 'undefined'){
								layer.load(3);
							}else{
								Global.blockUI({zIndex: 99999});	
							}
						},
						success : $formSuccess					
					});
				}catch(e){
					console.log(e);
				}
			}
		}

	};

	$.fn.handleForm = function (options, callback) {
		return this.each(function (i) {
			(new $.handleForm(this, options, callback));
		});
	};

})(jQuery);



