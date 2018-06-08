/* $.ajaxSetup({
 timeout: 30000
 }); */
$(document).ajaxError(function(d, e) {
	Global.unblockUI();
	Global.alert({
		"place": "prepend",
		"type": "warning",
		"message": '系统错误',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	});
});
$(document).ajaxSuccess(function(d, e) {
	if (e.status = '200' && e.readyState == '4' && e.responseJSON) {
		Global.unblockUI();
		if (e.responseJSON.id == '0004') { //没有登录或者登录超时
			bootbox.alert(e.responseJSON.info.text, function() {
				document.location.href = e.responseJSON.info.url;
			});
			return false;
		}
	}
});
/**
 常用的js做全局处理
 **/
var Global = function() {

	// 检测IE
	var isRTL = false;
	var isIE8 = false;
	var isIE9 = false;
	var isIE10 = false;

	var resizeHandlers = [];

	var assetsPath = '/frame/public/assets/';

	var globalImgPath = 'global/img/';

	var globalPluginsPath = 'global/plugins/';

	var globalCssPath = 'global/css/';

	var brandColors = {
		'blue': '#89C4F4',
		'red': '#F3565D',
		'green': '#1bbc9b',
		'purple': '#9b59b6',
		'grey': '#95a5a6',
		'yellow': '#F8CB00'
	};
	// init
	var handleInit = function() {

		if ($('body').css('direction') === 'rtl') {
			isRTL = true;
		}

		isIE8 = !! navigator.userAgent.match(/MSIE 8.0/);
		isIE9 = !! navigator.userAgent.match(/MSIE 9.0/);
		isIE10 = !! navigator.userAgent.match(/MSIE 10.0/);

		if (isIE10) {
			$('html').addClass('ie10'); // IE10 
		}

		if (isIE10 || isIE9 || isIE8) {
			$('html').addClass('ie'); // 低版本
		}
	};

	// 回调
	var _runResizeHandlers = function() {
		for (var i = 0; i < resizeHandlers.length; i++) {
			var each = resizeHandlers[i];
			each.call();
		}
	};

	// 改变页面size
	var handleOnResize = function() {
		var resize;
		if (isIE8) {
			var currheight;
			$(window).resize(function() {
				if (currheight == document.documentElement.clientHeight) {
					return;
				}
				if (resize) {
					clearTimeout(resize);
				}
				resize = setTimeout(function() {
					_runResizeHandlers();
				},
				50);
				currheight = document.documentElement.clientHeight; // store last body client height
			});
		} else {
			$(window).resize(function() {
				if (resize) {
					clearTimeout(resize);
				}
				resize = setTimeout(function() {
					_runResizeHandlers();
				},
				50);
			});
		}
	};
	//carousel
	var handleCarousel = function() {
		$('.carousel').carousel();
	}

	// portlet 
	var handlePortletTools = function() {
		// 关闭portlet
		$('body').on('click', '.portlet > .portlet-title > .tools > a.remove', function(e) {
			e.preventDefault();
			var portlet = $(this).closest(".portlet");

			if ($('body').hasClass('page-portlet-fullscreen')) {
				$('body').removeClass('page-portlet-fullscreen');
			}

			portlet.find('.portlet-title .fullscreen').tooltip('destroy');
			portlet.find('.portlet-title > .tools > .reload').tooltip('destroy');
			portlet.find('.portlet-title > .tools > .remove').tooltip('destroy');
			portlet.find('.portlet-title > .tools > .config').tooltip('destroy');
			portlet.find('.portlet-title > .tools > .collapse, .portlet > .portlet-title > .tools > .expand').tooltip('destroy');

			portlet.remove();
		});

		// 设为全屏
		$('body').on('click', '.portlet > .portlet-title .fullscreen', function(e) {
			e.preventDefault();
			var portlet = $(this).closest(".portlet");
			if (portlet.hasClass('portlet-fullscreen')) {
				$(this).removeClass('on');
				portlet.removeClass('portlet-fullscreen');
				$('body').removeClass('page-portlet-fullscreen');
				portlet.children('.portlet-body').css('height', 'auto');
			} else {
				var height = Global.getViewPort().height - portlet.children('.portlet-title').outerHeight() - parseInt(portlet.children('.portlet-body').css('padding-top')) - parseInt(portlet.children('.portlet-body').css('padding-bottom'));

				$(this).addClass('on');
				portlet.addClass('portlet-fullscreen');
				$('body').addClass('page-portlet-fullscreen');
				portlet.children('.portlet-body').css('height', height);
			}
		});
		//重新加载,注意在代码中返回的结果要为html格式
		$('body').on('click', '.portlet > .portlet-title > .tools > a.reload', function(e) {
			e.preventDefault();
			var el = $(this).closest(".portlet").children(".portlet-body");
			var url = $(this).attr("data-url");
			var error = $(this).attr("data-error-display");

			if (url) {
				Global.blockUI({
					target: el,
					animate: false,
					overlayColor: '#000000'
				});
				$.ajax({
					type: "GET",
					cache: false,
					url: url,
					dataType: "html",
					success: function(res) {

						Global.unblockUI(el);
						el.html(res);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						Global.unblockUI(el);
						var msg = '重新加载失败!';
						if (error == "toastr" && toastr) {
							toastr.error(msg);
						} else if (error == "notific8" && $.notific8) {
							$.notific8('zindex', 11500);
							$.notific8(msg, {
								theme: 'ruby',
								life: 3000
							});
						} else {
							alert(msg);
						}
					}
				});
			} else {
				//这里只是用于测试,实际生产过程中要做错误处理
				Global.blockUI({
					target: el,
					animate: false,
					overlayColor: '#000000'
				});
				window.setTimeout(function() {
					Global.unblockUI(el);
				},
				300000);
			}
		});

		// 处理用ajax加载的portlet
		$('.portlet .portlet-title a.reload[data-load="true"]').click();
		//切换portlet的内容显示/隐藏
		$('body').on('click', '.portlet > .portlet-title > .tools > .collapse, .portlet .portlet-title > .tools > .expand', function(e) {
			e.preventDefault();
			var el = $(this).closest(".portlet").children(".portlet-body");
			if ($(this).hasClass("collapse")) {
				$(this).removeClass("collapse").addClass("expand");
				el.slideUp(200);
			} else {
				$(this).removeClass("expand").addClass("collapse");
				el.slideDown(200);
			}
		});
	};

	// 单选框/复选框处理 //jQuery Uniform plugin
	var handleUniform = function() {
		
		if (!$().uniform) {
			return;
		}
		
		var test = $("input[type=checkbox]:not(.toggle, .md-check, .md-radiobtn, .make-switch, .icheck), input[type=radio]:not(.toggle, .md-check, .md-radiobtn, .star, .make-switch, .icheck)");

		if (test.size() > 0) {
			test.each(function() {
				if ($(this).parents(".checker").size() === 0) {
					$(this).show();
					$(this).uniform();
				}
			});
		}
	};

	// 开关样式
	var handleBootstrapSwitch = function() {
		if (!$().bootstrapSwitch) {
			return;
		}
		$('.make-switch').bootstrapSwitch();
	};

	//加减数字input
	var handleBootstrapTouchSpin = function() {
		if (!$().TouchSpin) {
			return;
		}
		$(".touchspin").each(function() {
			var tsOptions = $.extend({
				buttondown_class: 'btn blue',
				buttondown_txt: '<i class="fa fa-minus"></i>',
				buttonup_class: 'btn red',
				buttonup_txt: '<i class="fa fa-plus"></i>'
			},
			$(this).data());
			$(this).TouchSpin(tsOptions);
		});
	}

	// confirm对话框//Bootstrap
	var handleBootstrapConfirmation = function() {
		if (!$().confirmation) {
			return;
		}
		$('[data-toggle=confirmation]').confirmation({
			title: '确定吗?',
			container: 'body',
			btnOkLabel: '是',
			btnOkClass: 'btn-xs btn-success',
			btnCancelLabel: '否',
			btnCancelClass: 'btn-xs btn-danger'
		});
	}

	// 折叠插件//Bootstrap 
	var handleAccordions = function() {
		$('body').on('shown.bs.collapse', '.accordion.scrollable', function(e) {
			Global.scrollTo($(e.target));
		});
	};

	// Tabs//Bootstrap 
	var handleTabs = function() {
		//active
		if (window.location.hash) {
			var tabid = window.location.hash.substr(1);
			$('a[href="#' + tabid + '"]').parents('.tab-pane:hidden').each(function() {
				var tabid = $(this).attr("id");
				$('a[href="#' + tabid + '"]').click();
			});
			$('a[href="#' + tabid + '"]').click();
		}

		if ($().tabdrop) {
			$('.tabbable-tabdrop .nav-pills, .tabbable-tabdrop .nav-tabs').tabdrop({
				text: '<i class="fa fa-ellipsis-v"></i>&nbsp;<i class="fa fa-angle-down"></i>'
			});
		}
	};

	// 模态框//Bootstrap
	var handleModals = function() {
		// fix stackable modal issue: when 2 or more modals opened, closing one of modal will remove .modal-open class. 
		$('body').on('hide.bs.modal', function() {
			if ($('.modal:visible').size() > 1 && $('html').hasClass('modal-open') === false) {
				$('html').addClass('modal-open');
			} else if ($('.modal:visible').size() <= 1) {
				$('html').removeClass('modal-open');
			}
		});

		$('body').on('show.bs.modal', '.modal', function() {
			if ($(this).hasClass("modal-scroll")) {
				$('body').addClass("modal-open-noscroll");
			}
		});

		$('body').on('hide.bs.modal', '.modal', function() {
			$('body').removeClass("modal-open-noscroll");
		});

		$('body').on('hidden.bs.modal', '.modal:not(.modal-cached)', function() {
			$(this).removeData('bs.modal');
		});
	};

	// tooltips冒泡 //Bootstrap 
	var handleTooltips = function() {

		$('.tooltips').tooltip();

		// portlet tooltips
		$('.portlet > .portlet-title .fullscreen').tooltip({
			container: 'body',
			title: 'Fullscreen'
		});
		$('.portlet > .portlet-title > .tools > .reload').tooltip({
			container: 'body',
			title: 'Reload'
		});
		$('.portlet > .portlet-title > .tools > .remove').tooltip({
			container: 'body',
			title: 'Remove'
		});
		$('.portlet > .portlet-title > .tools > .config').tooltip({
			container: 'body',
			title: 'Settings'
		});
		$('.portlet > .portlet-title > .tools > .collapse, .portlet > .portlet-title > .tools > .expand').tooltip({
			container: 'body',
			title: 'Collapse/Expand'
		});
	};

	// Dropdowns下拉 //Bootstrap 
	var handleDropdowns = function() {

		$('body').on('click', '.dropdown-menu.hold-on-click', function(e) {
			e.stopPropagation();
		});
	};

	//提示内容区域
	var handleAlerts = function() {
		$('body').on('click', '[data-close="alert"]', function(e) {
			$(this).parent('.alert').hide();
			$(this).closest('.note').hide();
			e.preventDefault();
		});

		$('body').on('click', '[data-close="note"]', function(e) {
			$(this).closest('.note').hide();
			e.preventDefault();
		});

		$('body').on('click', '[data-remove="note"]', function(e) {
			$(this).closest('.note').remove();
			e.preventDefault();
		});
	};

	// Dropdowns下拉 hover触发 //Bootstrap 
	var handleDropdownHover = function() {
		$('[data-hover="dropdown"]').not('.hover-initialized').each(function() {
			$(this).dropdownHover();
			$(this).addClass('hover-initialized');
		});
	};

	// textarea自动调整大小 
	var handleTextareaAutosize = function() {
		if (typeof(autosize) == "function") {
			$('textarea.autosizeme').each(function() {
				autosize($(this));
			});
			//autosize(document.querySelector(''));
		}
	}

	//提示框	
	var handlePopovers = function() {

		$('.popovers').popover();

		$(document).on('click.bs.popover.data-api', function(e) {

			if (!$(e.target).hasClass('popovers') && $(e.target).attr('class') !== 'popover' && $(e.target).attr('class') !== 'popovers' && $(e.target).parents('[data-toggle="popover"]').length === 0 && $(e.target).parents('.popover.in').length === 0) {

				$('.popovers').popover('hide');
			} else {

				if ($('.popover.in').size() > 1) {
					$('.popover.in:not(:last)').popover('hide');
				}
			}
		});
	};

	// 处理模拟滚动条
	var handleScrollers = function() {
		Global.initSlimScroll('.scroller');
	};

	// 处理低版本IE的placeholder问题
	var handleFixInputPlaceholderForIE = function() {

		if (isIE8 || isIE9) { // ie8 & ie9
			$('input[placeholder]:not(.placeholder-no-fix), textarea[placeholder]:not(.placeholder-no-fix)').each(function() {
				var input = $(this);

				if (input.val() === '' && input.attr("placeholder") !== '') {
					input.addClass("placeholder").val(input.attr('placeholder'));
				}

				input.focus(function() {
					if (input.val() == input.attr('placeholder')) {
						input.val('');
					}
				});

				input.blur(function() {
					if (input.val() === '' || input.val() == input.attr('placeholder')) {
						input.val(input.attr('placeholder'));
					}
				});
			});
		}
	};

	// Select2 Dropdowns
	var handleSelect2 = function(el) {
		el = el || $(document);
		if ($().select2) {
			$('.select2me', $(el)).select2({
				placeholder: "请选择...",
				allowClear: true
			});
		}
	};

	//日期控件
	var handlePickers = function() {		
		if (!$().datepicker) {
			return;
		}
		$('.date-picker').each(function() {
		
			$(this).data('dateEndDate', '0d');
			var dateOptions = $.extend({
				autoclose: true
			},
			$(this).data());
			
            var datepicker = $(this);
			
            $().datepicker && $(this).datepicker(dateOptions)
			.on('changeDate', function(d){
				//console.log(d, this, d.date.getMonth());
				var date = d.date;
				var firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
				var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
				var iName = $('input:first', $(this)).prop('name');
				
				nextDateOptions = $.extend(dateOptions, {
					"dateEndDate": lastDay
				});
				//console.log(nextDateOptions, dateOptions);
				//$(this).next('.date-picker').data("dateEndDate", lastDay).datepicker($(this).next('.date-picker').data());
				$(this).next('.date-picker').datepicker('setEndDate', lastDay);
				$(this).next('.date-picker').datepicker('setStartDate', date);
				$(this).prev('.date-picker').datepicker('setStartDate', firstDay);
				$(this).prev('.date-picker').datepicker('setEndDate', date);

			});
			//console.log($('input:first', $(this)));
			$('i.fa-remove', this).parent().on('click', function(e) {
				e.preventDefault();
				$('input', $(datepicker)).val('');
				$(datepicker).datepicker('hide');
			});
		});

		$('.datetime-picker').each(function() {
			//console.log($(this));
			$(this).datetimepicker({
				format: 'YYYY-MM-DD HH:mm:ss',
				//defaultDate: new Date(),
				//minDate: new Date(),
				allowInputToggle: true,
				useStrict: true,
				sideBySide: true
			});

			/*             var datetimeOptions = $.extend({autoclose: true, format: 'yyyy-mm-dd hh:ii'}, $(this).data());
             console.log(datetimeOptions);
             var datetimepicker = $(this);
             $(this).datetimepicker(datetimeOptions); */
			/*             $('i.fa-remove', this).parent().on('click', function (e) {
             e.preventDefault();
             $('input', $(datetimepicker)).val('');
             $(datetimepicker).datetimepicker('hide');
             }); */
		});
	}

	//日期范围
	var handleDaterange = function() {
		//init date pickers
		$('.date-range').each(function() {
			var rangeInpit = $('input', this);
			$(this).daterangepicker({
				"locale": {
					//"format": "YYYY-MM-DD",
					"separator": " 至 ",
					"applyLabel": "确定",
					"cancelLabel": "取消",
					"fromLabel": "从",
					"toLabel": "至",
					"customRangeLabel": "自定义",
					"daysOfWeek": ["日", "一", "二", "三", "四", "五", "六"],
					"monthNames": ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一", "十二"],
					"firstDay": 1
				},
				format: 'YYYY-MM-DD',
				separator: ' 至 ',
				startDate: moment().subtract('days', 29),
				endDate: moment(),
				ranges: {
					'今天': [moment(), moment()],
					'昨天': [moment().subtract('days', 1), moment().subtract('days', 1)],
					'最近7天': [moment().subtract('days', 6), moment()],
					'最近30天': [moment().subtract('days', 29), moment()],
					'当前月': [moment().startOf('month'), moment().endOf('month')],
					'上一月': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
				},
				minDate: '01/01/2015',
				maxDate: '12/31/2028',
			},
			function(start, end) {
				$(rangeInpit).val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
			});
		});
	}
	//震动提醒
	var handlePulsate = function() {
		if (!jQuery().pulsate) {
			return;
		}

		if (Global.isIE8() == true) {
			return;
		}

		if (jQuery().pulsate) {
			$('.pulsate').each(function() {
				var pulsateOptions = $.extend({
					color: "#bf1c56",
					repeat: 3
				},
				$(this).data());
				//console.log(pulsateOptions);
				$(this).pulsate(pulsateOptions);
			});
		}
	}
	//图片查看
	var handleFancybox = function(el) {
		if (!jQuery.fancybox) {
			return;
		}
		el = el || $(document);

		if (jQuery(".fancybox-fast-view", el).length > 0) {
			jQuery(".fancybox-fast-view", el).fancybox();
		}

		
		if (jQuery(".fancybox-button", el).size() > 0) {
			jQuery(".fancybox-button", el).fancybox({
				groupAttr: 'data-rel',
				prevEffect: 'none',
				nextEffect: 'none',
				closeBtn: true,
				helpers: {
					title: {
						type: 'inside'
					}
				},
				beforeShow: function() {
					if ( !! $(this.element).data('extAdadsa')) {
						$(".fancybox-wrap").draggable({
							handle: ".fancybox-skin"
						});
					}
				},
				afterShow: function() {
					if ( !! $(this.element).data('extAdadsa')) {
						$('<a href="javascript:void(0);">左转90°</a>').on('click', function() {
							$('.fancybox-overlay.fancybox-overlay-fixed .fancybox-outer').css('transform', 'rotate(-90deg)');
						}).css({
							'margin-left': '10px'
						}).appendTo($('.fancybox-title'));
						$('<a href="javascript:void(0);">右转90°</a>').on('click', function() {
							$('.fancybox-overlay.fancybox-overlay-fixed .fancybox-outer').css('transform', 'rotate(90deg)');
						}).css({
							'margin-left': '10px'
						}).appendTo($('.fancybox-title'));
						$('<a href="javascript:void(0);">旋转180°</a>').on('click', function() {
							$('.fancybox-overlay.fancybox-overlay-fixed .fancybox-outer').css('transform', 'rotate(180deg)');
						}).css({
							'margin-left': '10px'
						}).appendTo($('.fancybox-title'));
						$('<a href="javascript:void(0);">还原</a>').on('click', function() {
							$('.fancybox-overlay.fancybox-overlay-fixed .fancybox-outer').css('transform', 'rotate(0deg)');
						}).css({
							'margin-left': '10px'
						}).appendTo($('.fancybox-title'));
					}
					/* 加上标识,防止重复加载方法 */

				}
			});

			$('.fancybox-video').fancybox({
				type: 'iframe'
			});
		}
	}

	var handleRotate = function(el, deg) {
		if (!el) {
			return false;
		}
		var transform = $(el).css('transform');
		if (transform == 'none') {
			$(el).css('transform', 'rotate(' + deg + 'deg)');
		} else {
			//console.log(transform);
		};
		//console.log($(el).css('transform'));
	}

	//select联动mcSelect
	var handleMcSelect = function(el) {
		el = el || $(document);
		if (!jQuery.mcSelect) {
			return;
		}
		$('.mcSelect', $(el)).mcSelect();
	}

	var handleBootstrapSelect = function(el) {
		el = el || $(document);

		/*         if (!jQuery.selectpicker) {
            return;
        } */
		$('.bs-select').each(function(i, e) {
			$(e).selectpicker({
				iconBase: 'fa',
				tickIcon: 'fa-check'
			});
		});
	}

	return {
		init: function() {
			handleInit();
			handleOnResize();
			handleUniform(); // radio & checkboxes
			handleTooltips(); // tooltips
			handlePortletTools(); // portlet
			handleTextareaAutosize(); //文本区域自动大小
			handleScrollers(); // 模拟滚动条
			handleModals(); // 模态框
			handleAlerts();
			handleAccordions();
			handleBootstrapSwitch();
			handleSelect2();
			handlePickers(); //日期控件
			handleDaterange();
			handleBootstrapConfirmation();
			handlePopovers();
			handleTabs();
			handleCarousel();
			handleDropdowns();
			handlePulsate();
			handleBootstrapTouchSpin();
			handleFixInputPlaceholderForIE();
			handleFancybox();
			handleMcSelect();
			handleBootstrapSelect();
		},
		//ajax完成之后需要重新加载的
		initAjax: function(el) {
			el = el || $(document);
			handleUniform(); // radio & checkboxes
			handleTooltips(); // tooltips
			//handlePortletTools(); // portlet
			handleTextareaAutosize(); //文本区域自动大小
			handleScrollers(); // 模拟滚动条
			handleAccordions();
			handleBootstrapSwitch();

			handleSelect2(el);
			handlePickers(); //日期控件
			handleDaterange();
			handleBootstrapConfirmation();
			handlePopovers();
			handleTabs();
			handleCarousel();
			handlePulsate();
			//handleDropdowns();
			handleBootstrapTouchSpin();
			handleDropdownHover();
			handleFancybox(el);
			handleMcSelect(el);
			handleBootstrapSelect(el);
		},
		initHandlePulsate: function() {
			handlePulsate();
		},
		initComponents: function() {
			this.initAjax();
		},
		//窗口大小
		addResizeHandler: function(func) {
			resizeHandlers.push(func);
		},
		runResizeHandlers: function() {
			_runResizeHandlers();
		},
		// loading遮罩
		blockUI: function(options) {			
			if(typeof layer != 'undefined'){
				layer.load(3);
			}else{
				options = $.extend(true, {
					cenrerY: true,
					boxed: true
				},
				options);

				var html = '';
				if (options.animate) {
					html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed': '') + '">' + '<div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>' + '</div>';
				} else if (options.iconOnly) {
					html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed': '') + '"><img src="' + this.getGlobalImgPath() + 'loading-spinner-grey.gif" align=""></div>';
				} else if (options.textOnly) {
					html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed': '') + '"><span>&nbsp;&nbsp;' + (options.message ? options.message: 'LOADING...') + '</span></div>';
				} else if (options.none) {
					html = '';
				} else {
					//html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed' : '') + '"><img src="' + this.getGlobalImgPath() + 'loading-spinner-grey.gif" align=""><span>&nbsp;&nbsp;' + (options.message ? options.message : 'LOADING...') + '</span></div>';
					html = '<div class="loading-message ' + (options.boxed ? 'loading-message-boxed': '') + '">' + '<h2 class="no-margin font-blue-steel inline">' + (options.message ? options.message: 'Loading') + '</h2><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>' + '</div>';

					//html = '<div class="loading-message font-blue' + (options.boxed ? 'loading-message-boxed' : '') + '"><i class="fa fa-spinner fa-spin fa-3x fa-fw margin-bottom font-blue"></i><span>&nbsp;&nbsp;' + (options.message ? options.message : 'LOADING...') + '</span></div>';
				}

				if (options.target) { // 对象遮罩
					var el = $(options.target);
					if (el.height() <= ($(window).height())) {
						options.cenrerY = true;
					}
					el.block({
						message: html,
						baseZ: options.zIndex ? options.zIndex: 10001,
						centerY: options.cenrerY !== undefined ? options.cenrerY: false,
						css: {
							top: '10%',
							border: '0',
							padding: '0',
							backgroundColor: 'none'
						},
						overlayCSS: {
							backgroundColor: options.overlayColor ? options.overlayColor: '#000000',
							opacity: options.boxed ? 0.1: 0.2,
							cursor: options.cursor ? options.cursor: 'wait'
						}
					});
				} else { // body遮罩
					$.blockUI({
						message: html,
						baseZ: options.zIndex ? options.zIndex: 10001,
						css: {
							border: '0',
							padding: '0',
							backgroundColor: 'none'
						},
						overlayCSS: {
							backgroundColor: options.overlayColor ? options.overlayColor: '#555',
							opacity: options.boxed ? 0.1: 0.2,
							cursor: options.cursor ? options.cursor: 'wait'
						}
					});
				}
			}
		},
		// 去除遮罩
		unblockUI: function(target) {
			if(typeof layer != 'undefined'){
				layer.closeAll();
			}else{
				if (target) {
					$(target).unblock({
						onUnblock: function() {
							$(target).css('position', '');
							$(target).css('zoom', '');
						}
					});
				} else {
					$.unblockUI();
				}
			}
		},
		startPageLoading: function(options) {
			if (options && options.animate) {
				$('.page-spinner-bar').remove();
				$('body').append('<div class="page-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>');
			} else {
				$('.page-loading').remove();
				$('body').append('<div class="page-loading"><img src="' + this.getGlobalImgPath() + 'loading-spinner-grey.gif"/>&nbsp;&nbsp;<span>' + (options && options.message ? options.message: 'Loading...') + '</span></div>');
			}
		},
		stopPageLoading: function() {
			$('.page-loading, .page-spinner-bar').remove();
		},
		//提示内容区域
		alert: function(options) {

			options = $.extend(true, {
				container: "",
				// alerts parent container(by default placed after the page breadcrumbs)
				place: "append",
				// "append" or "prepend" in container 
				type: 'success',
				// alert's type
				message: "",
				// alert's message
				close: true,
				// make alert closable
				reset: true,
				// close all previouse alerts first
				focus: true,
				// auto scroll to the alert after shown
				closeInSeconds: 0,
				// auto close after defined seconds
				icon: "" // put icon before the message
			},
			options);

			var id = Global.getUniqueID("Global_alert");

			var html = '<div id="' + id + '" class="Global-alerts alert alert-' + options.type + ' fade in">' + (options.close ? '<button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>': '') + (options.icon !== "" ? '<i class="fa-lg fa fa-' + options.icon + '"></i>  ': '') + options.message + '</div>';

			if (options.reset) {
				$('.Global-alerts').remove();
			}

			if (!options.container) {
				if ($('body').hasClass("page-container-bg-solid")) {
					$('.page-bar').after(html);
				} else {
					if ($('.page-bar').size() > 0) {
						$('.page-bar').after(html);
					} else {
						$('.page-breadcrumb').after(html);
					}
				}
			} else {
				if (options.place == "append") {
					$(options.container).append(html);
				} else {
					$(options.container).prepend(html);
				}
			}

			if (options.focus) {
				Global.scrollTo($('#' + id));
			}
			options.closeInSeconds = (options.closeInSeconds == 0) ? 10: options.closeInSeconds;
			if (options.closeInSeconds > 0) {
				setTimeout(function() {
					$('#' + id).remove();
				},
				options.closeInSeconds * 1000);
			}

            return id;
        },
        // 单选框/复选框
        initUniform: function (els) {
            if (els) {
                $(els).each(function () {
                    if ($(this).parents(".checker").size() === 0) {
                        $(this).show();
                        $.uniform && $(this).uniform();
                    }
                });
            } else {
                handleUniform();
            }
        },
        //
        updateUniform: function (els) {
            $.uniform.update(els);
        },
        getActualVal: function (el) {
            el = $(el);
            if (el.val() === el.attr("placeholder")) {
                return "";
            }
            return el.val();
        },
        //获取URL的参数及值
        getURLParameter: function (paramName) {
            var searchString = window.location.search.substring(1),
                    i, val, params = searchString.split("&");
			for (i = 0; i < params.length; i++) {
				val = params[i].split("=");
				if (val[0] == paramName) {
					return unescape(val[1]);
				}
			}
			return null;
		},
		// 触摸检测
		isTouchDevice: function() {
			try {
				document.createEvent("TouchEvent");
				return true;
			} catch(e) {
				return false;
			}
		},
		// 可视区域
		getViewPort: function() {
			var e = window,
			a = 'inner';
			if (! ('innerWidth' in window)) {
				a = 'client';
				e = document.documentElement || document.body;
			}

			return {
				width: e[a + 'Width'],
				height: e[a + 'Height']
			};
		},
		//
		scrollTo: function(el, offeset) {
			var pos = (el && el.size() > 0) ? el.offset().top: 0;
			pos = ($('.breadcrumbs').size() > 0) ? pos - 70 : pos;
			if (el) {
				if ($('body').hasClass('page-header-fixed')) {
					pos = pos - $('.page-header').height();
				} else if ($('body').hasClass('page-header-top-fixed')) {
					pos = pos - $('.page-header-top').height();
				} else if ($('body').hasClass('page-header-menu-fixed')) {
					pos = pos - $('.page-header-menu').height();
				}
				pos = pos + (offeset ? offeset: - 1 * el.height());
			}

			$('html,body').animate({
				scrollTop: pos
			},
			'slow');
		},
		//模拟滚动条
		initSlimScroll: function(el) {
			$(el).each(function() {
				if ($(this).attr("data-initialized")) {
					return;
				}

				var height;

				if ($(this).attr("data-height")) {
					height = $(this).attr("data-height");
				} else {
					height = $(this).css('height');
				}

				$(this).slimScroll({
					allowPageScroll: true,
					size: '7px',
					color: ($(this).attr("data-handle-color") ? $(this).attr("data-handle-color") : '#bbb'),
					wrapperClass: ($(this).attr("data-wrapper-class") ? $(this).attr("data-wrapper-class") : 'slimScrollDiv'),
					railColor: ($(this).attr("data-rail-color") ? $(this).attr("data-rail-color") : '#eaeaea'),
					position: isRTL ? 'left': 'right',
					height: height,
					alwaysVisible: ($(this).attr("data-always-visible") == "1" ? true: false),
					railVisible: ($(this).attr("data-rail-visible") == "1" ? true: false),
					disableFadeOut: true
				});

				$(this).attr("data-initialized", "1");
			});
		},
		destroySlimScroll: function(el) {
			$(el).each(function() {
				if ($(this).attr("data-initialized") === "1") {
					$(this).removeAttr("data-initialized");
					$(this).removeAttr("style");

					var attrList = {};

					if ($(this).attr("data-handle-color")) {
						attrList["data-handle-color"] = $(this).attr("data-handle-color");
					}
					if ($(this).attr("data-wrapper-class")) {
						attrList["data-wrapper-class"] = $(this).attr("data-wrapper-class");
					}
					if ($(this).attr("data-rail-color")) {
						attrList["data-rail-color"] = $(this).attr("data-rail-color");
					}
					if ($(this).attr("data-always-visible")) {
						attrList["data-always-visible"] = $(this).attr("data-always-visible");
					}
					if ($(this).attr("data-rail-visible")) {
						attrList["data-rail-visible"] = $(this).attr("data-rail-visible");
					}

					$(this).slimScroll({
						wrapperClass: ($(this).attr("data-wrapper-class") ? $(this).attr("data-wrapper-class") : 'slimScrollDiv'),
						destroy: true
					});

					var the = $(this);

					$.each(attrList, function(key, value) {
						the.attr(key, value);
					});

				}
			});
		},
		ajaxclick: function(el) {
			el = (typeof el === 'object') ? el: $('#menu_' + el);
			if (!$(el).parents('li').hasClass('open')) {
				$(el).parents('li').find('a').trigger('click');
			};
			$('a.ajaxify', $(el)).trigger('click');
		},
		ajaxify: function(el, url) {
			var resBreakpointMd = this.getResponsiveBreakpoint('md');
			if (typeof el === 'object') {
				var url = url || $(el).attr("href");
			} else {
				var url = el;
				el = null;
			}
			//var url = url || $(el).attr("href");
			var menuContainer = $('.page-sidebar ul');
			var pageContent = $('.page-content');
			var pageContentBody = $('.page-content .page-content-body');

			menuContainer.children('li.active').removeClass('active');
			menuContainer.children('arrow.open').removeClass('open');

			if (el) {
				$(el).parents('li').each(function() {
					$(el).addClass('active');
					$(el).children('a > span.arrow').addClass('open');
				});

				$(el).parents('li').addClass('active');
			}

			if (Global.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page 
				$('.page-header .responsive-toggler').click();
			}

			Global.blockUI(); //startPageLoading();
			//var the = ;
			$.ajax({
				type: "POST",
				cache: false,
				data: {
					'_ajax': 1
				},
				url: url,
				dataType: "html",
				success: function(res) {

					if (/^\{\"id\"\:\"\d+\"\,\"msg\"[\s\S]+?\}$/.test(res)) { //返回的其实是一段json字符串,一般是权限问题
						res = JSON.parse(res);

						if (res.info && typeof res.info == 'object') {
							if (res.info.text) {
								bootbox.alert(res.info.text, function() {
									Global.unblockUI();
									if (res.info.url) {
										document.location.href = res.info.url;
									}
								});
							}
						} else {
							bootbox.alert(res.msg, function() {
								Global.unblockUI();
							});
						}
						return false;
					}
					if (el && $(el).parents('li.open').size() === 0) {
						$('.page-sidebar-menu > li.open > a').click();
					}
					//return false;
					/* setTimeout(function(){ */
					Global.unblockUI(); //stopPageLoading();
					pageContentBody.html('');
					pageContentBody.append($(res));

					Global.initAjax(); // initialize core stuff						
					/* }, 500); */
				},
				error: function(xhr, ajaxOptions, thrownError) {
					Global.unblockUI();
					pageContentBody.html('<h4>页面不存在!</h4>');
				}
			});
		},
		//扩展ajax添加一个统一的callback
		ajax: function(options, callback) {
			var defaults = {
				success: function(data) {

					callback && callback(data);

				}
			};
			$.extend(options, defaults);
			return $.ajax(options);
		},
		// scroll to the top
		scrollTop: function() {
			Global.scrollTo();
		},
		//生成id
		getUniqueID: function(prefix) {
			return prefix + '_' + Math.floor(Math.random() * (new Date()).getTime());
		},
		// IE8 
		isIE8: function() {
			return isIE8;
		},
		// IE9
		isIE9: function() {
			return isIE9;
		},
		//RTL 
		isRTL: function() {
			return isRTL;
		},
		getAssetsPath: function() {
			return assetsPath;
		},
		setAssetsPath: function(path) {
			assetsPath = path;
		},
		setGlobalImgPath: function(path) {
			globalImgPath = path;
		},
		getGlobalImgPath: function() {
			return assetsPath + globalImgPath;
		},
		setGlobalPluginsPath: function(path) {
			globalPluginsPath = path;
		},
		getGlobalPluginsPath: function() {
			return assetsPath + globalPluginsPath;
		},
		getGlobalCssPath: function() {
			return assetsPath + globalCssPath;
		},
		getBrandColor: function(name) {
			if (brandColors[name]) {
				return brandColors[name];
			} else {
				return '';
			}
		},
		getResponsiveBreakpoint: function(size) {
			var sizes = {
				'xs': 480,
				// extra small
				'sm': 768,
				// small
				'md': 992,
				// medium
				'lg': 1200 // large
			};

			return sizes[size] ? sizes[size] : 0;
		},
		text2Html: function(str) {
			return String(str).replace(/&amp;/g, '&').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
		},
		viewSiteMsg: function(id, callback) {
			$.ajax({
				type: "POST",
				cache: false,
				data: {
					'_ajax': 1,
					id: id
				},
				url: '/public/view.json',
				dataType: "json",
				success: function(res) {
					Global.unblockUI();
					if (res.id == '1001') {
						res.info.content = Global.text2Html(res.info.content);
						template.config('escape', false);
						var html = template('sitemsg-modal-temp', res.info);
						$('#global-modal-content').html(html);
						Global.initAjax($('#global-modal-content'));
						$('#global-modal').modal();
						callback && callback();
					} else {
						bootbox.alert(res.msg);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					Global.unblockUI();
					bootbox.alert("读取信息错误!");
				}
			});
		},
		initFancybox: function(el) {
			handleFancybox(el);
		},
		initRegion: function(parent) {
			parent = parent || $('body');
			if ($('.region', $(parent)).length > 0) {
				$.ajax({
					'url': '/public/area.json',
					'dataType': 'json',
					'success': function(res) {
						if (res.id == '1001') {
							var provinces = res.info;
							$('.region', $(parent)).each(function(i, el) {
								var v = $('select:last', $(el)).data('val');
								if (v != undefined) {
									v += '';
									$('select:first', el).data('val', v.substring(0, 2));
									$('select:eq(1)', el).data('val', v.substring(0, 4));
								}
								var s1 = $('select:first', el);

								s1.find('option:gt(0)').remove();
								s1.append(function() {
									var opts = '';
									for (e in provinces) {
										opts += '<option value="' + e + '">' + provinces[e]['name'] + '</option>';

									}
									return $(opts);
								});

								$('select', el).each(function(k) {
									$(this).on('change', function() {
										var nextSelect = $('select:eq(' + (k + 1) + ')', $(el));
										if (nextSelect.length > 0) {
											$.ajax({
												'url': '/public/area.json',
												'data': {
													id: $(this).val()
												},
												'dataType': 'json',
												'success': function(res) {
													if (res.id == '1001') {
														nextSelect.find('option:gt(0)').remove();
														nextSelect.append(function() {
															var opts = '';
															for (e in res.info) {
																var selected = ''; //$(this).data('val') == e ? ' selected="selected"' : '';
																opts += '<option value="' + e + '"' + selected + '>' + res.info[e]['name'] + '</option>';
															}
															return $(opts);
														});
														var sval = nextSelect.data('val');

														if (sval != undefined && sval != '') {
															$("option[value='" + sval + "']", nextSelect).prop('selected', true);
															nextSelect.change();
														}
													}
												}
											});
										} else {}
									});

								});

								if (s1.data('val') != undefined && s1.data('val') != '') {
									$("option[value='" + s1.data('val') + "']", s1).prop('selected', true);
									s1.change();
								}
							});
						}
					}
				});
			}
		},
		initIndustry: function(parent) {
			parent = parent || $('body');
			if ($('.industry', $(parent)).length > 0) {
				$.ajax({
					'url': '/public/industry.json',
					'dataType': 'json',
					'success': function(res) {
						if (res.id == '1001') {
							var provinces = res.info;
							$('.industry', $(parent)).each(function(i, el) {
								var v = $('select:last', $(el)).data('val');
								if (v != undefined) {
									v += '';
									$('select:first', el).data('val', v.substring(0, 2));
									$('select:eq(1)', el).data('val', v.substring(0, 4));
								}
								var s1 = $('select:first', el);

								s1.find('option:gt(0)').remove();
								s1.append(function() {
									var opts = '';
									for (e in provinces) {
										opts += '<option value="' + e + '">' + provinces[e]['name'] + '</option>';

									}
									return $(opts);
								});

								$('select', el).each(function(k) {
									$(this).on('change', function() {
										var nextSelect = $('select:eq(' + (k + 1) + ')', $(el));
										if (nextSelect.length > 0) {
											$.ajax({
												'url': '/public/industry.json',
												'data': {
													id: $(this).val()
												},
												'dataType': 'json',
												'success': function(res) {
													if (res.id == '1001') {
														nextSelect.find('option:gt(0)').remove();
														nextSelect.append(function() {
															var opts = '';
															for (e in res.info) {
																var selected = ''; //$(this).data('val') == e ? ' selected="selected"' : '';
																opts += '<option value="' + e + '"' + selected + '>' + res.info[e]['name'] + '</option>';
															}
															return $(opts);
														});
														var sval = nextSelect.data('val');

														if (sval != undefined && sval != '') {
															$("option[value='" + sval + "']", nextSelect).prop('selected', true);
															nextSelect.change();
														}
													}
												}
											});
										} else {}
									});

								});

								if (s1.data('val') != undefined && s1.data('val') != '') {
									$("option[value='" + s1.data('val') + "']", s1).prop('selected', true);
									s1.change();
								}
							});
						}
					}
				});
			}
		},
		post: function(url, postData, callBack) {
			Global.blockUI();
			//格式化全站的 ajax post
			postData._ajax = 1;
			//$.post(url + '&_=' + Math.random(), postData, callBack, 'json');
			$.ajax({
				type: "POST",
				cache: false,
				data: postData,
				url: url,
				dataType: "json",
				success: function(res) {
					Global.unblockUI();
					callBack(res);
				},
				error: function(xhr, ajaxOptions, thrownError) {
					Global.unblockUI();
				}
			});
		},
		show: function(t, m, f) {
			toastr.options = {
				"positionClass": "toast-bottom-right"
			}
			f = (f) ? f: 'success';
			toastr[f](m, t);
		}
	};

} ();

/*     $(document).ajaxError(function(event, request, settings) {
 console.log('eeeee');
 });
 
 
 $.ajaxSetup({
 timeout: 2000 //Time in milliseconds
 }); */

/* String的正则检测扩展方法 
 *	检测数字之前需转化为String 
 */
String.prototype.matchs = function(type, arg1, arg2, arg3) {
	switch (type) {
	case 'isMobile':
		//手机号码
		return ! (this.match(/^0?(13[0-9]|15[012356789]|17[0678]|18[0-9]|14[57])[0-9]{8}$/) === null);
		break;
	case 'isPhone':
		//电话号码 020-88888888 02088888888 (020)-88888888 020 88888888
		return ! (this.match(/^(\d{3,4}|\(\d{3,4}\))[\-\s]?\d{7,8}$/) === null);
		break;
	case 'isIntelPhone':
		//国际电话号码 +111-222 555-888//不保证精度,只是匹配了国家码部分,号码部分是 ' ','-',数字的组合即可
		return ! (this.match(/^\+?\d{1,3}([\-\s]?\d{0,4})?([\-]\d+|[\s]\d+)+$/) === null);
		break;
	case 'isEmail':
		//Email
		return ! (this.match(/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/) === null);
		break;
	case 'isInt':
		//整数 arg1:最小位数; arg2:最大位数 
		arg1 = arg1 || 1;
		arg2 = arg2 || 25;
		arg1--;
		arg2--;
		var reg = new RegExp('^-?[1-9][0-9]{' + arg1 + ',' + arg2 + '}$');
		return ! (this.match(reg) === null);
		break;
	case 'isFloat':
		//浮点数 arg1:整数部分最小位数; arg2:整数部分最大位数; arg3:小数部分最大位数
		arg1 = arg1 || 1;
		arg2 = arg2 || 25;
		arg1--;
		arg2--;
		arg3 = arg3 || 25;
		var reg = new RegExp('^-?((0(\.[0-9]{1,' + arg3 + '})?)|([1-9][0-9]{' + arg1 + ',' + arg2 + '}(\.[0-9]{1,' + arg3 + '})?))$');
		return ! (this.match(reg) === null);
		break;
	case 'isMoney':
		//金额
		return ! (this.match(/^([0-9]+|[0-9]{1,3}(,[0-9]{3})*)(\.[0-9]{1,2})?$/) === null);
		break;
	case 'isCn':
		//中文
		return ! (this.match(/^[\u4e00-\u9fa5]{0,}$/) === null);
		break;
	case 'isChar':
		//由数字和26个英文字母组成的字符串
		return ! (this.match(/^[A-Za-z0-9]+$/) === null);
		break;
	case 'isCharUL':
		//由数字、26个英文字母或者下划线组成的字符串
		return ! (this.match(/^\w+$/) === null);
		break;
	case 'isUrl':
		//url链接			
		return ! (this.match(/(([a-z]{3,6}:\/\/)|(^|\s))([a-zA-Z0-9\-]+\.)+[a-z]{2,13}[\.\?\=\&\%\/\w\-]*\b([^@]|$)/) === null);
		break;
	case 'isID':
		//身份证号,去掉了15位的验证,二代证没有15位的了
		//return !(this.match(/^(1[1-5]|2[1-3]|3[1-7]|4[1-6]|5[1-4]|6[1-5])(\d{4})((1[8-9][0-9]{2}|2[0](0[0-9]|1[0-5]))(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])\d{3}[0-9xX]|\d{2}(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])\d{3})$/) === null);
		return ! (this.match(/^(1[1-5]|2[1-3]|3[1-7]|4[1-6]|5[0-4]|6[1-5]|71|8[12]|91)(\d{4})(1[8-9][0-9]{2}|20\d{2})(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])\d{3}[0-9xX]$/) === null);
		break;
	case 'isZip':
		//中国邮政编码
		return ! (this.match(/^[1-9]\d{5}(?!\d)$/) === null);
		/* 		case 'isGfw'://关键字过滤
             return !(this.match(/?!.*((操你)|(她妈)|(它妈)|(他妈)|(你妈)|(妈逼)|(妈B)|(fuck)|(去死)|(贱人)|(妈B)|(叼)|(擦)|(戳)|(CAO)|(TMD)|(尼玛)|(垃圾)|(屁)|(龌龊)|(SB)|(煞笔)|(傻B)|(祖宗)|(白痴)|(吃屎)|(香港占中)|(香港中环)|(香港民主)|(香港大学生)|(客服)|(云(\s|\*|\-|\||\@|\#|\&|\%|\.)*联))).*$/i) === null);
             break; */

	}
};

/* 身份证号验证的校验方法,在之前正则表达式的基础上加了日期校验和末尾校验,去掉了15位的验证,二代证没有15位的了 */
String.prototype.isIdCardNo = function() {
	if (!this.matchs('isID')) {
		return false;
	}

	var factorArr = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1);
	var parityBit = new Array("1", "0", "X", "9", "8", "7", "6", "5", "4", "3", "2");
	var varArray = new Array();

	var lngProduct = 0;
	var intCheckDigit;

	//校验日期
	if (!moment(this.substring(6, 14), 'YYYYMMDD').isValid()) {
		return false;
	}

	// 校验数组
	for (i = 0; i < 18; i++) {
		if (i < 17) {
			varArray[i] = this.charAt(i) * factorArr[i];
		} else {
			varArray[i] = this.charAt(i).toUpperCase();
		}
	}

	// 校验数值
	for (i = 0; i < 17; i++) {
		lngProduct = lngProduct + varArray[i];
	}

	// 校验数字求余,末尾校验
	intCheckDigit = parityBit[lngProduct % 11];

	if (varArray[17] !== intCheckDigit) {
		return false;
	}
	return true;
}

/* 香港身份证号验证的校验方法 
 逻辑关系：
 先把首位字母改为数字，即A为1，B为2，C为3...Z为26，再乘以8；然后把字母后面的6个数字依次乘以7、6、5、4、3、2；再将以上所有乘积相加的和，除以11，得到余数；如果整除，则括号中的校验码为0，如果余数为1，则校验码为A，如果余数为2～10，则用11减去这个余数的差作校验码。
 例如：P103265（1），P，在字母表中排行16，则以16代表，则计算校验码：
 16×8＋1×7＋0×6＋3×5＋2×4＋6×3＋5×2＝186
 186÷11＝16......余10
 11－10＝1，即校验码为1。
 */
String.prototype.isHKIdCardNo = function() {
	var the = this;
	the = the.toLowerCase(); //全部转为小写;
	if (!/^([a-z]\d{6})[\(]?([0-9a]){1}[\)]?$/i.test(the)) {
		return false;
	}
	var matches = the.match(/^([a-z]\d{6})[\(]?([0-9a]){1}[\)]?$/i);

	var varArray = matches[1].split('');
	var checkNum = 0;
	checkNum = (varArray[0].charCodeAt(0) % 96) * 8;

	for (var i = 1, len = varArray.length; i < len; i++) {

		checkNum = checkNum - (0 - (8 - i) * varArray[i]);
	}

	checkNum = checkNum % 11;
	switch (checkNum) {
	case 0:
		checkNum = '0';
		break;
	case 1:
		checkNum = 'a';
		break;
	default:
		checkNum = 11 - checkNum + '';
		break;
	}

	return matches[2] == checkNum;

}
/* 将数字转换成金额 1234569.1234 => ￥1,234,569.12  */
Number.prototype.formatMoney = function(places, symbol, thousand, decimal) {
	places = ! isNaN(places = Math.abs(places)) ? places: 2;
	symbol = symbol !== undefined ? symbol: "￥";
	thousand = thousand || ",";
	decimal = decimal || ".";
	var number = this,
	negative = number < 0 ? "-": "",
	i = parseInt(number = Math.abs( + number || 0).toFixed(places), 10) + "",
	j = (j = i.length) > 3 ? j % 3: 0;
	//console.log(i, j);
	return symbol + negative + (j ? i.substr(0, j) + thousand: "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : "");
};

/* 将文本格式化输出 */
String.prototype.formatOuter = function(len, symbol, title) {
	var str = this;
	len = (isNaN(len - 0)) ? str.length * 2: len;
	title = title || true;
	symbol = symbol || '...';
	var strArray = str.split('');
	var strLength = 0;
	var newStr = ''; //新字符串
	var oldStr = str + ''; //原字符串
	var hasCut = false; //是否被截断
	if (len > str.length * 2) { //如果最大长度大于字符长度*2,那么不要执行以下了
		newStr = oldStr;
	} else {
		for (var i = 0, l = strArray.length; i < l; i++) {
			if (strArray[i].match(/([\u3002\uff1b\uff0c\uff1a\u201c\u201d\uff08\uff09\u3001\uff1f\u300a\u300b]|[\u4e00-\u9fa5])/) === null) {
				strLength++;
			} else { //中文或者中文标点			
				strLength += 2;
			}

			if (strLength >= len) { //如果长度大于len
				var hasCut = true;
				break;
			} else {
				newStr += strArray[i];
			}
		}
	}

	if (hasCut) {
		newStr = newStr + symbol;
	}
	if (title) { //是否显示title
		//return '<span class="tooltips" data-container="body" data-placement="top" data-original-title="'+oldStr+'">'+newStr+'</span>';
		return '<span title="' + oldStr + '">' + newStr + '</span>';
	} else {
		return newStr;
	}
}
/* 数字大写 */
Number.prototype.cnMoney = function() {
	var number = this;
    var fraction = ['角', '分'];
    var digit = [
        '零', '壹', '贰', '叁', '肆',
        '伍', '陆', '柒', '捌', '玖'
    ];
    var unit = [
        ['元', '万', '亿'],
        ['', '拾', '佰', '仟']
    ];
    var head = number < 0 ? '欠' : '';
    number = Math.abs(number);
    var s = '';
    for (var i = 0; i < fraction.length; i++) {
        s += (digit[Math.floor(number * 10 * Math.pow(10, i)) % 10] + fraction[i]).replace(/零./, '');
    }
    s = s || '整';
    number = Math.floor(number);
    for (var i = 0; i < unit[0].length && number > 0; i++) {
        var p = '';
        for (var j = 0; j < unit[1].length && number > 0; j++) {
            p = digit[number % 10] + unit[1][j] + p;
            number = Math.floor(number / 10);
        }
        s = p.replace(/(零.)*零$/, '').replace(/^$/, '零') + unit[0][i] + s;
    }
    return head + s.replace(/(零.)*零元/, '元')
        .replace(/(零.)+/g, '零')
        .replace(/^整$/, '零元整');
};