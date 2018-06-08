/*
 *  Document   : app.js
 *  Author     : pixelcave
 */
var App = function() {
	var e, a, s, t, i, l, o, n, r = function() {
		e = $("#page-container"),
		a = $("#page-content"),
		s = $("header"),
		t = $("#page-content + footer"),
		i = $("#sidebar"),
		l = $("#sidebar-scroll"),
		o = $("#sidebar-alt"),
		n = $("#sidebar-alt-scroll"),
		p("init"),
		h(),
		u(),
		f(),
		m(),
		g(),
		$(window).resize(function() {
			g()
		}),
		$(window).bind("orientationchange", g);
		var r = $("#year-copy"),
		c = new Date;
		r.html(2014 === c.getFullYear() ? "2014": "2014-" + c.getFullYear().toString().substr(2, 2)),
		v(),
		$('[data-toggle="tabs"] a, .enable-tabs a').click(function(e) {
			e.preventDefault(),
			$(this).tab("show")
		}),
		$('[data-toggle="tooltip"], .enable-tooltip').tooltip({
			container: "body",
			animation: !1
		}),
		$('[data-toggle="popover"], .enable-popover').popover({
			container: "body",
			animation: !0
		}),
		$('[data-toggle="lightbox-image"]').magnificPopup({
			type: "image",
			image: {
				titleSrc: "title"
			}
		}),
		$('[data-toggle="lightbox-gallery"]').magnificPopup({
			delegate: "a.gallery-link",
			type: "image",
			gallery: {
				enabled: !0,
				navigateByImgClick: !0,
				arrowMarkup: '<button type="button" class="mfp-arrow mfp-arrow-%dir%" title="%title%"></button>',
				tPrev: "Previous",
				tNext: "Next",
				tCounter: '<span class="mfp-counter">%curr% of %total%</span>'
			},
			image: {
				titleSrc: "title"
			}
		}),
		$(".textarea-editor").wysihtml5(),
		$(".select-chosen").chosen({
			width: "100%"
		}),
		$(".select-select2").select2(),
		$(".input-slider").slider(),
		$(".input-tags").tagsInput({
			width: "auto",
			height: "auto"
		}),
		$(".input-datepicker, .input-daterange").datepicker({
			weekStart: 1
		}),
		$(".input-datepicker-close").datepicker({
			weekStart: 1
		}).on("changeDate",
		function() {
			$(this).datepicker("hide")
		}),
		$(".input-timepicker").timepicker({
			minuteStep: 1,
			showSeconds: !0,
			showMeridian: !0
		}),
		$(".input-timepicker24").timepicker({
			minuteStep: 1,
			showSeconds: !0,
			showMeridian: !1
		}),
		$(".pie-chart").easyPieChart({
			barColor: $(this).data("bar-color") ? $(this).data("bar-color") : "#777777",
			trackColor: $(this).data("track-color") ? $(this).data("track-color") : "#eeeeee",
			lineWidth: $(this).data("line-width") ? $(this).data("line-width") : 3,
			size: $(this).data("size") ? $(this).data("size") : "80",
			animate: 800,
			scaleColor: !1
		}),
		$("input, textarea").placeholder()
	},
	c = function() {
		var e = $("#page-wrapper");
		e.hasClass("page-loading") && e.removeClass("page-loading")
	},
	d = function() {
		return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth
	},
	h = function() {
		var a = 250,
		s = 250,
		t = $(".sidebar-nav-menu"),
		i = $(".sidebar-nav-submenu");
		t.click(function() {
			var t = $(this);
			return e.hasClass("sidebar-mini") && e.hasClass("sidebar-visible-lg-mini") && d() > 991 ? t.hasClass("open") ? t.removeClass("open") : ($(".sidebar-nav-menu.open").removeClass("open"), t.addClass("open")) : t.parent().hasClass("active") || (t.hasClass("open") ? (t.removeClass("open").next().slideUp(a,
			function() {
				b(t, 200, 300)
			}), setTimeout(g, a)) : ($(".sidebar-nav-menu.open").removeClass("open").next().slideUp(a), t.addClass("open").next().slideDown(s,
			function() {
				b(t, 150, 600)
			}), setTimeout(g, a > s ? a: s))),
			!1
		}),
		i.click(function() {
			var e = $(this);
			return e.parent().hasClass("active") !== !0 && (e.hasClass("open") ? (e.removeClass("open").next().slideUp(a,
			function() {
				b(e, 200, 300)
			}), setTimeout(g, a)) : (e.closest("ul").find(".sidebar-nav-submenu.open").removeClass("open").next().slideUp(a), e.addClass("open").next().slideDown(s,
			function() {
				b(e, 150, 600)
			}), setTimeout(g, a > s ? a: s))),
			!1
		})
	},
	b = function(a, t, i) {
		if (!e.hasClass("disable-menu-autoscroll")) {
			var l;
			if (s.hasClass("navbar-fixed-top") || s.hasClass("navbar-fixed-bottom")) {
				var o = a.parents("#sidebar-scroll"),
				n = a.offset().top + Math.abs($("div:first", o).offset().top);
				l = n - t > 0 ? n - t: 0,
				o.animate({
					scrollTop: l
				},
				i)
			} else {
				var r = a.offset().top;
				l = r - t > 0 ? r - t: 0,
				$("html, body").animate({
					scrollTop: l
				},
				i)
			}
		}
	},
	p = function(a, s) {
		if ("init" === a) p("sidebar-scroll"),
		p("sidebar-alt-scroll"),
		$(".sidebar-partial #sidebar").mouseenter(function() {
			p("close-sidebar-alt")
		}),
		$(".sidebar-alt-partial #sidebar-alt").mouseenter(function() {
			p("close-sidebar")
		});
		else {
			var t = d();
			if ("toggle-sidebar" === a) t > 991 ? (e.toggleClass("sidebar-visible-lg"), e.hasClass("sidebar-mini") && e.toggleClass("sidebar-visible-lg-mini"), e.hasClass("sidebar-visible-lg") && p("close-sidebar-alt"), "toggle-other" === s && (e.hasClass("sidebar-visible-lg") || p("open-sidebar-alt"))) : (e.toggleClass("sidebar-visible-xs"), e.hasClass("sidebar-visible-xs") && p("close-sidebar-alt")),
			p("sidebar-scroll");
			else if ("toggle-sidebar-alt" === a) t > 991 ? (e.toggleClass("sidebar-alt-visible-lg"), e.hasClass("sidebar-alt-visible-lg") && p("close-sidebar"), "toggle-other" === s && (e.hasClass("sidebar-alt-visible-lg") || p("open-sidebar"))) : (e.toggleClass("sidebar-alt-visible-xs"), e.hasClass("sidebar-alt-visible-xs") && p("close-sidebar"));
			else if ("open-sidebar" === a) t > 991 ? (e.hasClass("sidebar-mini") && e.removeClass("sidebar-visible-lg-mini"), e.addClass("sidebar-visible-lg")) : e.addClass("sidebar-visible-xs"),
			p("close-sidebar-alt");
			else if ("open-sidebar-alt" === a) e.addClass(t > 991 ? "sidebar-alt-visible-lg": "sidebar-alt-visible-xs"),
			p("close-sidebar");
			else if ("close-sidebar" === a) t > 991 ? (e.removeClass("sidebar-visible-lg"), e.hasClass("sidebar-mini") && e.addClass("sidebar-visible-lg-mini")) : e.removeClass("sidebar-visible-xs");
			else if ("close-sidebar-alt" === a) e.removeClass(t > 991 ? "sidebar-alt-visible-lg": "sidebar-alt-visible-xs");
			else if ("sidebar-scroll" == a) {
				if (e.hasClass("sidebar-mini") && e.hasClass("sidebar-visible-lg-mini") && t > 991) l.length && l.parent(".slimScrollDiv").length && (l.slimScroll({
					destroy: !0
				}), l.attr("style", ""));
				else if (e.hasClass("header-fixed-top") || e.hasClass("header-fixed-bottom")) {
					var i = $(window).height();
					if (l.length && !l.parent(".slimScrollDiv").length) {
						l.slimScroll({
							height: i,
							color: "#fff",
							size: "3px",
							touchScrollStep: 100
						});
						var o;
						$(window).on("resize orientationchange",
						function() {
							clearTimeout(o),
							o = setTimeout(function() {
								p("sidebar-scroll")
							},
							150)
						})
					} else l.add(l.parent()).css("height", i)
				}
			} else if ("sidebar-alt-scroll" == a && (e.hasClass("header-fixed-top") || e.hasClass("header-fixed-bottom"))) {
				var r = $(window).height();
				if (n.length && !n.parent(".slimScrollDiv").length) {
					n.slimScroll({
						height: r,
						color: "#fff",
						size: "3px",
						touchScrollStep: 100
					});
					var c;
					$(window).on("resize orientationchange",
					function() {
						clearTimeout(c),
						c = setTimeout(function() {
							p("sidebar-alt-scroll")
						},
						150)
					})
				} else n.add(n.parent()).css("height", r)
			}
		}
		return ! 1
	},
	g = function() {
		var l = $(window).height(),
		n = i.outerHeight(),
		r = o.outerHeight(),
		c = s.outerHeight(),
		d = t.outerHeight();
		s.hasClass("navbar-fixed-top") || s.hasClass("navbar-fixed-bottom") || l > n && l > r ? e.hasClass("footer-fixed") ? a.css("min-height", l - c + "px") : a.css("min-height", l - (c + d) + "px") : e.hasClass("footer-fixed") ? a.css("min-height", (n > r ? n: r) - c + "px") : a.css("min-height", (n > r ? n: r) - (c + d) + "px")
	},
	u = function() {
		$('[data-toggle="block-toggle-content"]').on("click",
		function() {
			var e = $(this).closest(".block").find(".block-content");
			$(this).hasClass("active") ? e.slideDown() : e.slideUp(),
			$(this).toggleClass("active")
		}),
		$('[data-toggle="block-toggle-fullscreen"]').on("click",
		function() {
			var e = $(this).closest(".block");
			$(this).hasClass("active") ? e.removeClass("block-fullscreen") : e.addClass("block-fullscreen"),
			$(this).toggleClass("active")
		}),
		$('[data-toggle="block-hide"]').on("click",
		function() {
			$(this).closest(".block").fadeOut()
		})
	},
	f = function() {
		var e = $("#to-top");
		$(window).scroll(function() {
			$(this).scrollTop() > 150 && d() > 991 ? e.fadeIn(100) : e.fadeOut(100)
		}),
		e.click(function() {
			return $("html, body").animate({
				scrollTop: 0
			},
			400),
			!1
		})
	},
	v = function() {
		var e = $(".chat-users"),
		a = $(".chat-talk"),
		s = $(".chat-talk-messages"),
		t = $("#sidebar-chat-message"),
		i = "";
		$(".chat-talk-messages").slimScroll({
			height: 210,
			color: "#fff",
			size: "3px",
			position: "left",
			touchScrollStep: 100
		}),
		$("a", e).click(function() {
			return e.slideUp(),
			a.slideDown(),
			t.focus(),
			!1
		}),
		$("#chat-talk-close-btn").click(function() {
			return a.slideUp(),
			e.slideDown(),
			!1
		}),
		$("#sidebar-chat-form").submit(function(e) {
			i = t.val(),
			i && (s.append('<li class="chat-talk-msg chat-talk-msg-highlight themed-border animation-slideLeft">' + $("<div />").text(i).html() + "</li>"), s.animate({
				scrollTop: s[0].scrollHeight
			},
			500), t.val("")),
			e.preventDefault()
		})
	},
	m = function() {
		var a, t = $(".sidebar-themes"),
		i = $("#theme-link");
		i.length && (a = i.attr("href"), $("li", t).removeClass("active"), $('a[data-theme="' + a + '"]', t).parent("li").addClass("active")),
		$("a", t).click(function() {
			a = $(this).data("theme"),
			$("li", t).removeClass("active"),
			$(this).parent("li").addClass("active"),
			"default" === a ? i.length && (i.remove(), i = $("#theme-link")) : i.length ? i.attr("href", a) : ($('link[href="css/themes-3.0.css"]').before('<link id="theme-link" rel="stylesheet" href="' + a + '">'), i = $("#theme-link"))
		}),
		$(".dropdown-options a").click(function(e) {
			e.stopPropagation()
		});
		var l = $("#options-main-style"),
		o = $("#options-main-style-alt");
		e.hasClass("style-alt") ? o.addClass("active") : l.addClass("active"),
		l.click(function() {
			e.removeClass("style-alt"),
			$(this).addClass("active"),
			o.removeClass("active")
		}),
		o.click(function() {
			e.addClass("style-alt"),
			$(this).addClass("active"),
			l.removeClass("active")
		});
		var n = $("#options-header-default"),
		r = $("#options-header-inverse");
		s.hasClass("navbar-default") ? n.addClass("active") : r.addClass("active"),
		n.click(function() {
			s.removeClass("navbar-inverse").addClass("navbar-default"),
			$(this).addClass("active"),
			r.removeClass("active")
		}),
		r.click(function() {
			s.removeClass("navbar-default").addClass("navbar-inverse"),
			$(this).addClass("active"),
			n.removeClass("active")
		})
	},
	C = function() {
		$.extend(!0, $.fn.dataTable.defaults, {
			sDom: "<'row'<'col-sm-6 col-xs-5'l><'col-sm-6 col-xs-7'f>r>t<'row'<'col-sm-5 hidden-xs'i><'col-sm-7 col-xs-12 clearfix'p>>",
			sPaginationType: "bootstrap",
			oLanguage: {
				sLengthMenu: "_MENU_",
				sSearch: '<div class="input-group">_INPUT_<span class="input-group-addon"><i class="fa fa-search"></i></span></div>',
				sInfo: "<strong>_START_</strong>-<strong>_END_</strong> of <strong>_TOTAL_</strong>",
				oPaginate: {
					sPrevious: "",
					sNext: ""
				}
			}
		}),
		$.extend($.fn.dataTableExt.oStdClasses, {
			sWrapper: "dataTables_wrapper form-inline",
			sFilterInput: "form-control",
			sLengthSelect: "form-control"
		})
	};
	return {
		init: function() {
			r(),
			c()
		},
		sidebar: function(e, a) {
			p(e, a)
		},
		datatables: function() {
			C()
		}
	}
} ();
$(function() {
	App.init()
});