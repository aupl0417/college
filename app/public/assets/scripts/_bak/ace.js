/**
 Required. Ace's Basic File to Initiliaze Different Parts and Some Variables.
*/


//some basic variables
(function(undefined) {
	if( !('ace' in window) ) window['ace'] = {}
	if( !('helper' in window['ace']) ) window['ace'].helper = {}
	if( !('vars' in window['ace']) ) window['ace'].vars = {}
	window['ace'].vars['icon'] = ' ace-icon ';
	window['ace'].vars['.icon'] = '.ace-icon';

	ace.vars['touch']	= ('ontouchstart' in window);//(('ontouchstart' in document.documentElement) || (window.DocumentTouch && document instanceof DocumentTouch));
	
	//sometimes the only good way to work around browser's pecularities is to detect them using user-agents
	//though it's not accurate
	var agent = navigator.userAgent
	ace.vars['webkit'] = !!agent.match(/AppleWebKit/i)
	ace.vars['safari'] = !!agent.match(/Safari/i) && !agent.match(/Chrome/i);
	ace.vars['android'] = ace.vars['safari'] && !!agent.match(/Android/i)
	ace.vars['ios_safari'] = !!agent.match(/OS ([4-9])(_\d)+ like Mac OS X/i) && !agent.match(/CriOS/i)
	
	ace.vars['ie'] = window.navigator.msPointerEnabled || (document.all && document.querySelector);//8-11
	ace.vars['old_ie'] = document.all && !document.addEventListener;//8 and below
	ace.vars['very_old_ie']	= document.all && !document.querySelector;//7 and below
	ace.vars['firefox'] = 'MozAppearance' in document.documentElement.style;
	
	ace.vars['non_auto_fixed'] = ace.vars['android'] || ace.vars['ios_safari'];
	
	
	//sometimes we try to use 'tap' event instead of 'click' if jquery mobile plugin is available
	ace['click_event'] = ace.vars['touch'] && jQuery.fn.tap ? 'tap' : 'click';
})();



//some ace helper functions
(function($$ , undefined) {//$$ is ace.helper
 $$.unCamelCase = function(str) {
	return str.replace(/([a-z])([A-Z])/g, function(match, c1, c2){ return c1+'-'+c2.toLowerCase() })
 }
 $$.strToVal = function(str) {
	var res = str.match(/^(?:(true)|(false)|(null)|(\-?[\d]+(?:\.[\d]+)?)|(\[.*\]|\{.*\}))$/i);

	var val = str;
	if(res) {
		if(res[1]) val = true;
		else if(res[2]) val = false;
		else if(res[3]) val = null;	
		else if(res[4]) val = parseFloat(str);
		else if(res[5]) {
			try { val = JSON.parse(str) }
			catch (err) {}
		}
	}

	return val;
 }
 $$.getAttrSettings = function(elem, attr_list, prefix) {
	if(!elem) return;
	var list_type = attr_list instanceof Array ? 1 : 2;
	//attr_list can be Array or Object(key/value)
	var prefix = prefix ? prefix.replace(/([^\-])$/ , '$1-') : '';
	prefix = 'data-' + prefix;

	var settings = {}
	for(var li in attr_list) if(attr_list.hasOwnProperty(li)) {
		var name = list_type == 1 ? attr_list[li] : li;
		var attr_val, attr_name = $$.unCamelCase(name.replace(/[^A-Za-z0-9]{1,}/g , '-')).toLowerCase()

		if( ! ((attr_val = elem.getAttribute(prefix + attr_name))  ) ) continue;
		settings[name] = $$.strToVal(attr_val);
	}

	return settings;
 }

 $$.scrollTop = function() {
	return document.scrollTop || document.documentElement.scrollTop || document.body.scrollTop
 }
 $$.winHeight = function() {
	return window.innerHeight || document.documentElement.clientHeight;
 }
 $$.redraw = function(elem, force) {
	if(!elem) return;
	var saved_val = elem.style['display'];
	elem.style.display = 'none';
	elem.offsetHeight;
	if(force !== true) {
		elem.style.display = saved_val;
	}
	else {
		//force redraw for example in old IE
		setTimeout(function() {
			elem.style.display = saved_val;
		}, 10);
	}
 }
})(ace.helper);

/**
 <b>Scroll to top button</b>.
*/
(function($ , undefined) {

	//the scroll to top button
	var scroll_btn = $('.btn-scroll-up');
	if(scroll_btn.length > 0) {
		var is_visible = false;
		$(window).on('scroll.scroll_btn', function() {
			var scroll = ace.helper.scrollTop();
			var h = ace.helper.winHeight();
			var body_sH = document.body.scrollHeight;
			if(scroll > parseInt(h / 4) || (scroll > 0 && body_sH >= h && h + scroll >= body_sH - 1)) {//|| for smaller pages, when reached end of page
				if(!is_visible) {
					scroll_btn.addClass('display');
					is_visible = true;
				}
			} else {
				if(is_visible) {
					scroll_btn.removeClass('display');
					is_visible = false;
				}
			}
		}).triggerHandler('scroll.scroll_btn');

		scroll_btn.on(ace.click_event, function(){
			var duration = Math.min(500, Math.max(100, parseInt(ace.helper.scrollTop() / 3)));
			$('html,body').animate({scrollTop: 0}, duration);
			return false;
		});
	}
	
})(window.jQuery);

/**
 <b>Load content via Ajax </b>. For more information please refer to documentation #basics/ajax
*/

(function($ , undefined) {
	var ajax_loaded_scripts = {}

	function AceAjax(contentArea, settings) {
		var $contentArea = $(contentArea);
		var self = this;
		$contentArea.attr('data-ajax-content', 'true');
		
		//get a list of 'data-*' attributes that override 'defaults' and 'settings'
		var attrib_values = ace.helper.getAttrSettings(contentArea, $.fn.ace_ajax.defaults);
		this.settings = $.extend({}, $.fn.ace_ajax.defaults, settings, attrib_values);


		var working = false;
		var $overlay = $();//empty set

		this.force_reload = false;//set jQuery ajax's cache option to 'false' to reload content
		this.loadUrl = function(hash, cache, manual_trigger) {
			var url = false;
			hash = hash.replace(/^(\#\!)?\#/, '');
			
			this.force_reload = (cache === false)
			
			if(typeof this.settings.content_url === 'function') url = this.settings.content_url(hash);
			if(typeof url === 'string') this.getUrl(url, hash, manual_trigger);
		}
		
		this.loadAddr = function(url, hash, cache) {
			this.force_reload = (cache === false);
			this.getUrl(url, hash, false);
		}


		this.reload = function() {
			var hash = $.trim(window.location.hash);
			if(!hash && this.settings.default_url) hash = this.settings.default_url;
			
			this.loadUrl(hash, false);
		}
		this.post = function(url, data, updateView, extraParams) {
			var url = url || $.trim(location.href.replace(location.hash,''));
			if(!url) return;
			var data = data || {}
			var updateView = updateView || false;
			this.getUrl(url, null, false, 'POST', data, updateView, extraParams);
		}
		
		
		this.getUrl = function(url, hash, manual_trigger, method, data, updateView, extraParams) {
			if(working) {
				return;
			}
			
			var method = method || 'GET';
			var updateView = (method == 'GET') || (method == 'POST' && updateView == true)
			var data = data || null;
		
			var event
			$contentArea.trigger(event = $.Event('ajaxloadstart'), {url: url, hash: hash, method: method, data: data})
			if (event.isDefaultPrevented()) return;
			
			self.startLoading();
			
			
			var ajax_params = method == 'GET' ? {'url': url, 'cache': !this.force_reload} : {'url': url, 'method' : 'POST', 'data': data}
			if(method == 'POST' && typeof extraParams == 'object') ajax_params = $.extend({}, ajax_params, extraParams);

			$.ajax(ajax_params)
			.error(function() {
				$contentArea.trigger('ajaxloaderror', {url: url, hash: hash, method: method, data: data});
				
				self.stopLoading(true);
			})
			.done(function(result) {
				$contentArea.trigger('ajaxloaddone', {url: url, hash: hash, method: method, data: data});
				if(method == 'POST') {
					var event
					$contentArea.trigger(event = $.Event('ajaxpostdone', {url: url, data: data, result: result}))
					if( event.isDefaultPrevented() ) updateView = false;
				}
				
				
				var link_element = null, link_text = '';
				if(typeof self.settings.update_active === 'function') {
					link_element = self.settings.update_active.call(null, hash, url, method, updateView);
				}
				else if(self.settings.update_active === true && hash) {
					link_element = $('a[data-url="'+hash+'"]');
					if(link_element.length > 0) {
						var nav = link_element.closest('.nav');
						if(nav.length > 0) {
							nav.find('.active').each(function(){
								var $class = 'active';
								if( $(this).hasClass('hover') || self.settings.close_active ) $class += ' open';
								
								$(this).removeClass($class);							
								if(self.settings.close_active) {
									$(this).find(' > .submenu').css('display', '');
								}
							})
							
							var active_li = link_element.closest('li').addClass('active').parents('.nav li').addClass('active open');
							nav.closest('.sidebar[data-sidebar-scroll=true]').each(function() {
								var $this = $(this);
								$this.ace_sidebar_scroll('reset');
								if(manual_trigger == true) $this.ace_sidebar_scroll('scroll_to_active');//first time only
							})
						}
					}
				}

				/////////
				if(typeof self.settings.update_breadcrumbs === 'function') {
					link_text = self.settings.update_breadcrumbs.call(null, hash, url, link_element, method, updateView);
				}
				else if(self.settings.update_breadcrumbs === true && link_element != null && link_element.length > 0) {
					link_text = updateBreadcrumbs(link_element);
				}
				/////////
				
				$overlay.addClass('content-loaded').detach();
				if(updateView) {
					//convert "title" and "link" tags to "div" tags for later processing
					result = String(result)
						.replace(/<(title|link)([\s\>])/gi,'<div class="hidden ajax-append-$1"$2')
						.replace(/<\/(title|link)\>/gi,'</div>')
					$contentArea.empty().html(result);
				}
				
				$(self.settings.loading_overlay || $contentArea).append($overlay);



				//remove previous stylesheets inserted via ajax
				if(updateView) setTimeout(function() {
					$('head').find('link.ace-ajax-stylesheet').remove();

					var main_selectors = ['link.ace-main-stylesheet', 'link#main-ace-style', 'link[href*="/ace.min.css"]', 'link[href*="/ace.css"]']
					var ace_style = [];
					for(var m = 0; m < main_selectors.length; m++) {
						ace_style = $('head').find(main_selectors[m]).first();
						if(ace_style.length > 0) break;
					}
					
					$contentArea.find('.ajax-append-link').each(function(e) {
						var $link = $(this);
						if ( $link.attr('href') ) {
							var new_link = jQuery('<link />', {type : 'text/css', rel: 'stylesheet', 'class': 'ace-ajax-stylesheet'})
							if( ace_style.length > 0 ) new_link.insertBefore(ace_style);
							else new_link.appendTo('head');
							new_link.attr('href', $link.attr('href'));//we set "href" after insertion, for IE to work
						}
						$link.remove();
					})
				}, 10);

				//////////////////////

				if(typeof self.settings.update_title === 'function') {
					self.settings.update_title.call(null, hash, url, link_text, method, updateView);
				}
				else if(self.settings.update_title === true && method == 'GET') {
					updateTitle(link_text);
				}

				if( !manual_trigger && updateView ) {
					$('html,body').animate({scrollTop: 0}, 250);
				}

				//////////////////////
				$contentArea.trigger('ajaxloadcomplete', {url: url, hash: hash, method: method, data:data});
				//////////////////////
				
				
				//if result contains call to "loadScripts" then don't stopLoading now
				var re = /\.(?:\s*)ace(?:_a|A)jax(?:\s*)\((?:\s*)(?:\'|\")loadScripts(?:\'|\")/;
				if(result.match(re)) self.stopLoading();
				else self.stopLoading(true);
			})
		}
		
		
		///////////////////////
		var fixPos = false;
		var loadTimer = null;
		this.startLoading = function() {
			if(working) return;
			working = true;
			
			if(!this.settings.loading_overlay && $contentArea.css('position') == 'static') {
				$contentArea.css('position', 'relative');//for correct icon positioning
				fixPos = true;
			}
				
			$overlay.remove();
			$overlay = $('<div class="ajax-loading-overlay"><i class="ajax-loading-icon '+(this.settings.loading_icon || '')+'"></i> '+this.settings.loading_text+'</div>')

			if(this.settings.loading_overlay == 'body') $('body').append($overlay.addClass('ajax-overlay-body'));
			else if(this.settings.loading_overlay) $(this.settings.loading_overlay).append($overlay);
			else $contentArea.append($overlay);

			
			if(this.settings.max_load_wait !== false) 
			 loadTimer = setTimeout(function() {
				loadTimer = null;
				if(!working) return;
				
				var event
				$contentArea.trigger(event = $.Event('ajaxloadlong'))
				if (event.isDefaultPrevented()) return;
				
				self.stopLoading(true);
			 }, this.settings.max_load_wait * 1000);
		}
		
		this.stopLoading = function(stopNow) {
			if(stopNow === true) {
				working = false;
				
				$overlay.remove();
				if(fixPos) {
					$contentArea.css('position', '');//restore previous 'position' value
					fixPos = false;
				}
				
				if(loadTimer != null) {
					clearTimeout(loadTimer);
					loadTimer = null;
				}
			}
			else {
				$overlay.addClass('almost-loaded');
				
				$contentArea.one('ajaxscriptsloaded.inner_call', function() {
					self.stopLoading(true);
					/**
					if(window.Pace && Pace.running == true) {
						Pace.off('done');
						Pace.once('done', function() { self.stopLoading(true) })
					}
					else self.stopLoading(true);
					*/
				})
			}
		}
		
		this.working = function() {
			return working;
		}
		///////////////////////
		
		
		
		function updateBreadcrumbs(link_element) {
			var link_text = '';
		 
			//update breadcrumbs
			var breadcrumbs = $('.breadcrumb');
			if(breadcrumbs.length > 0 && breadcrumbs.is(':visible')) {
				breadcrumbs.find('> li:not(:first-child)').remove();

				var i = 0;		
				link_element.parents('.nav li').each(function() {
					var link = $(this).find('> a');
					
					var link_clone = link.clone();
					link_clone.find('i,.fa,.glyphicon,.ace-icon,.menu-icon,.badge,.label').remove();
					var text = link_clone.text();
					link_clone.remove();
					
					var href = link.attr('href');

					if(i == 0) {
						var li = $('<li class="active"></li>').appendTo(breadcrumbs);
						li.text(text);
						link_text = text;
					}
					else {
						var li = $('<li><a /></li>').insertAfter(breadcrumbs.find('> li:first-child'));
						li.find('a').attr('href', href).text(text);
					}
					i++;
				})
			}
			
			return link_text;
		 }
		 
		 function updateTitle(link_text) {
			var $title = $contentArea.find('.ajax-append-title');
			if($title.length > 0) {
				document.title = $title.text();
				$title.remove();
			}
			else if(link_text.length > 0) {
				var extra = $.trim(String(document.title).replace(/^(.*)[\-]/, ''));//for example like " - Ace Admin"
				if(extra) extra = ' - ' + extra;
				link_text = $.trim(link_text) + extra;
			}
		 }
		 
		 
		 this.loadScripts = function(scripts, callback) {
			var scripts = scripts || [];
			$.ajaxPrefilter('script', function(opts) {opts.cache = true});
			setTimeout(function() {
				//let's keep a list of loaded scripts so that we don't load them more than once!
				
				function finishLoading() {
					if(typeof callback === 'function') callback();
					$('.btn-group[data-toggle="buttons"] > .btn').button();
					
					$contentArea.trigger('ajaxscriptsloaded');
				}
				
				//var deferreds = [];
				var deferred_count = 0;//deferreds count
				var resolved = 0;
				for(var i = 0; i < scripts.length; i++) if(scripts[i]) {
					(function() {
						var script_name = "js-"+scripts[i].replace(/[^\w\d\-]/g, '-').replace(/\-\-/g, '-');
						if( ajax_loaded_scripts[script_name] !== true )	deferred_count++;
					})()
				}
				

				function nextScript(index) {
					index += 1;
					if(index < scripts.length) loadScript(index);
					else {
						finishLoading();
					}
				}
				
				function loadScript(index) {
					index = index || 0;
					if(!scripts[index]) {//could be null sometimes
						return nextScript(index);
					}
				
					var script_name = "js-"+scripts[index].replace(/[^\w\d\-]/g, '-').replace(/\-\-/g, '-');
					//only load scripts that are not loaded yet!
					if( ajax_loaded_scripts[script_name] !== true ) {
						$.getScript(scripts[index])
						.done(function() {
							ajax_loaded_scripts[script_name] = true;
						})
						//.fail(function() {
						//})
						.complete(function() {
							resolved++;
							if(resolved >= deferred_count && working) {
								finishLoading();
							}
							else {
								nextScript(index);
							}
						})
					}
					else {//script previoisly loaded
						nextScript(index);
					}
				}
				
				
				if (deferred_count > 0) {
					loadScript();
				}
				else {
					finishLoading();
				}

			}, 10)
		}
		
		
		
		/////////////////
		$(window)
		.off('hashchange.ace_ajax')
		.on('hashchange.ace_ajax', function(e, manual_trigger) {
			var hash = $.trim(window.location.hash);
			if(!hash || hash.length == 0) return;
			
			if(self.settings.close_mobile_menu) {
				try {$(self.settings.close_mobile_menu).ace_sidebar('mobileHide')} catch(e){}
			}
			if(self.settings.close_dropdowns) {
				$('.dropdown.open .dropdown-toggle').dropdown('toggle');
			}
			
			self.loadUrl(hash, null, manual_trigger);
		}).trigger('hashchange.ace_ajax', [true]);
		
		var hash = $.trim(window.location.hash);
		if(!hash && this.settings.default_url) window.location.hash = this.settings.default_url;

	}//AceAjax



	$.fn.aceAjax = $.fn.ace_ajax = function (option, value, value2, value3, value4) {
		var method_call;

		var $set = this.each(function () {
			var $this = $(this);
			var data = $this.data('ace_ajax');
			var options = typeof option === 'object' && option;

			if (!data) $this.data('ace_ajax', (data = new AceAjax(this, options)));
			if (typeof option === 'string' && typeof data[option] === 'function') {
				if(value4 !== undefined) method_call = data[option](value, value2, value3, value4);
				else if(value3 !== undefined) method_call = data[option](value, value2, value3);
				else if(value2 !== undefined) method_call = data[option](value, value2);
				else method_call = data[option](value);
			}
		});

		return (method_call === undefined) ? $set : method_call;
	}
	
	
	
	$.fn.aceAjax.defaults = $.fn.ace_ajax.defaults = {
		content_url: false,
		default_url: false,
		loading_icon: 'fa fa-spin fa-spinner fa-2x orange',
		loading_text: '',
		loading_overlay: null,
		update_breadcrumbs: true,
		update_title: true,
		update_active: true,
		close_active: false,
		max_load_wait: false,
		close_mobile_menu: false,
		close_dropdowns: false
     }

})(window.jQuery);



/**
 <b>Custom drag event for touch devices</b> used in scrollbars.
 For better touch event handling and extra options a more advanced solution such as <u>Hammer.js</u> is recommended.
*/

//based on but not dependent on jQuery mobile
/*
* jQuery Mobile v1.3.2
* http://jquerymobile.com
*
* Copyright 2010, 2013 jQuery Foundation, Inc. and other contributors
* Released under the MIT license.
* http://jquery.org/license
*
*/
(function($ , undefined) {

	if(!ace.vars['touch']) return;

	var touchStartEvent = "touchstart MSPointerDown pointerdown",// : "mousedown",
			touchStopEvent  =  "touchend touchcancel MSPointerUp MSPointerCancel pointerup pointercancel",// : "mouseup",
			touchMoveEvent  =  "touchmove MSPointerMove MSPointerHover pointermove";// : "mousemove";


	$.event.special.ace_drag = {
		setup: function() {
			var min_threshold = 0;
		
			var $this = $(this);
			$this.on(touchStartEvent, function(event) {		
				var data = event.originalEvent.touches ?
					event.originalEvent.touches[ 0 ] :
					event,
					start = {
						//time: Date.now(),
						coords: [ data.pageX, data.pageY ],
						origin: $(event.target)
					},
					stop;
					//start.origin.trigger({'type' : 'ace_dragStart', 'start':(start || [-1,-1])});
					
					var direction = false, dx = 0, dy = 0;

				function moveHandler(event) {
					if (!start) {
						return;
					}
					var data = event.originalEvent.touches ?
							event.originalEvent.touches[ 0 ] :
							event;
					stop = {
						coords: [ data.pageX, data.pageY ]
					};
					
					// prevent scrolling
					//if ( Math.abs(start.coords[1] - stop.coords[1]) > 0 || Math.abs(start.coords[0] - stop.coords[01]) > 0 ) {
						//event.preventDefault();
					//}


					if (start && stop) {
						dx = 0;
						dy = 0;

						direction = 
							(
							 Math.abs(dy = start.coords[ 1 ] - stop.coords[ 1 ]) > min_threshold
								&& 
							 Math.abs(dx = start.coords[ 0 ] - stop.coords[ 0 ]) <= Math.abs(dy)
							)
							? 
							(dy > 0 ? 'up' : 'down')
							:
							(
							 Math.abs(dx = start.coords[ 0 ] - stop.coords[ 0 ]) > min_threshold
								&& 
							 Math.abs( dy ) <= Math.abs(dx)
							)
							?
							(dx > 0 ? 'left' : 'right')
							:
							false;
							

							if( direction !== false ) {
							 var retval = {cancel: false}
							 start.origin.trigger({
								'type': 'ace_drag',
								//'start': start.coords,
								//'stop': stop.coords,
								'direction': direction,
								'dx': dx,
								'dy': dy,
								'retval': retval
							 })

		 					  // prevent document scrolling unless retval.cancel == true
							  if( retval.cancel == false ) event.preventDefault();
							}
					}
					start.coords[0] = stop.coords[0];
					start.coords[1] = stop.coords[1];
				}

				$this
				.on(touchMoveEvent, moveHandler)
				.one(touchStopEvent, function(event) {
					$this.off(touchMoveEvent, moveHandler);
					//start.origin.trigger({'type' : 'ace_dragEnd', 'stop':(stop || [-1,-1])});
					
					start = stop = undefined;
				
				});
			});
		}
	}

})(window.jQuery);

/**
 <b>Sidebar functions</b>. Collapsing/expanding, toggling mobile view menu and other sidebar functions.
*/

(function($ , undefined) {
	var sidebar_count = 0;

	function Sidebar(sidebar, settings) {
		var self = this;
		this.$sidebar = $(sidebar);
		this.$sidebar.attr('data-sidebar', 'true');
		if( !this.$sidebar.attr('id') ) this.$sidebar.attr( 'id' , 'id-sidebar-'+(++sidebar_count) )

		
		//get a list of 'data-*' attributes that override 'defaults' and 'settings'
		var attrib_values = ace.helper.getAttrSettings(sidebar, $.fn.ace_sidebar.defaults, 'sidebar-');
		this.settings = $.extend({}, $.fn.ace_sidebar.defaults, settings, attrib_values);


		//some vars
		this.minimized = false;//will be initialized later
		this.collapsible = false;//...
		this.horizontal = false;//...
		this.mobile_view = false;//


		//return an array containing sidebar state variables
		this.vars = function() {
			return {'minimized': this.minimized, 'collapsible': this.collapsible, 'horizontal': this.horizontal, 'mobile_view': this.mobile_view}
		}
		this.get = function(name) {
			if(this.hasOwnProperty(name)) return this[name];
		}
		this.set = function(name, value) {
			if(this.hasOwnProperty(name)) this[name] = value;
		}
		

		//return a reference to self (sidebar instance)
		this.ref = function() {
			return this;
		}

		
		//toggle icon for sidebar collapse/expand button
		var toggleIcon = function(minimized, save) {
			var icon = $(this).find(ace.vars['.icon']), icon1, icon2;
			if(icon.length > 0) {
				icon1 = icon.attr('data-icon1');//the icon for expanded state
				icon2 = icon.attr('data-icon2');//the icon for collapsed state

				if(typeof minimized !== "undefined") {
					if(minimized) icon.removeClass(icon1).addClass(icon2);
					else icon.removeClass(icon2).addClass(icon1);
				}
				else {
					icon.toggleClass(icon1).toggleClass(icon2);
				}
				
				try {
					if(save !== false) ace.settings.saveState(icon.get(0));
				} catch(e) {}
			}
		}
		
		//if not specified, find the toggle button related to this sidebar
		var findToggleBtn = function() {
			var toggle_btn = self.$sidebar.find('.sidebar-collapse');
			if(toggle_btn.length == 0) toggle_btn = $('.sidebar-collapse[data-target="#'+(self.$sidebar.attr('id')||'')+'"]');
			if(toggle_btn.length != 0) toggle_btn = toggle_btn[0];
			else toggle_btn = null;
			
			return toggle_btn;
		}
		
		
		//collapse/expand sidebar
		this.toggleMenu = function(toggle_btn, save) {
			if(this.collapsible) return false;

			this.minimized = !this.minimized;
			var save = !(toggle_btn === false || save === false);
			
		
			if(this.minimized) this.$sidebar.addClass('menu-min');
			else this.$sidebar.removeClass('menu-min');

			try {
				if(save) ace.settings.saveState(sidebar, 'class', 'menu-min', this.minimized);
			} catch(e) {}
		
			if( !toggle_btn ) {
				toggle_btn = findToggleBtn();
			}
			if(toggle_btn) {
				toggleIcon.call(toggle_btn, this.minimized, save);
			}

			//force redraw for ie8
			if(ace.vars['old_ie']) ace.helper.redraw(sidebar);
			
			
			$(document).trigger('settings.ace', ['sidebar_collapsed' , this.minimized, sidebar, save]);
			
			if( this.minimized ) this.$sidebar.trigger($.Event('collapse.ace.sidebar'));
			else this.$sidebar.trigger($.Event('expand.ace.sidebar'));
			
			
			return true;
		}
		this.collapse = function(toggle_btn, save) {
			if(this.collapsible) return;
			
			this.minimized = false;
			this.toggleMenu(toggle_btn, save) 
		}
		this.expand = function(toggle_btn, save) {
			if(this.collapsible) return;
			
			this.minimized = true;
			this.toggleMenu(toggle_btn, save);
		}
		

		
		this.showResponsive = function() {
			this.$sidebar.removeClass(responsive_min_class).removeClass(responsive_max_class);
		}
		
		//collapse/expand in 2nd mobile style
		this.toggleResponsive = function(toggle_btn, showMenu) {
			if( !this.mobile_view || this.mobile_style != 3 ) return;
		
			if( this.$sidebar.hasClass('menu-min') ) {
				//remove menu-min because it interferes with responsive-max
				this.$sidebar.removeClass('menu-min');
				var btn = findToggleBtn();
				if(btn) toggleIcon.call(btn);
			}


			var showMenu = typeof showMenu === 'boolean' ? showMenu : (typeof toggle_btn === 'boolean' ? toggle_btn : this.$sidebar.hasClass(responsive_min_class));

			if(showMenu) {
				this.$sidebar.addClass(responsive_max_class).removeClass(responsive_min_class);
			}
			else {
				this.$sidebar.removeClass(responsive_max_class).addClass(responsive_min_class);
			}
			this.minimized = !showMenu;


			if( !toggle_btn || typeof toggle_btn !== 'object' ) {
				toggle_btn = this.$sidebar.find('.sidebar-expand');
				if(toggle_btn.length == 0) toggle_btn = $('.sidebar-expand[data-target="#'+(this.$sidebar.attr('id')||'')+'"]');
				if(toggle_btn.length != 0) toggle_btn = toggle_btn[0];
				else toggle_btn = null;
			}
			
			if(toggle_btn) {
				var icon = $(toggle_btn).find(ace.vars['.icon']), icon1, icon2;
				if(icon.length > 0) {
					icon1 = icon.attr('data-icon1');//the icon for expanded state
					icon2 = icon.attr('data-icon2');//the icon for collapsed state

					if(!showMenu) icon.removeClass(icon2).addClass(icon1);
					else icon.removeClass(icon1).addClass(icon2);
				}
			}

			if(showMenu) self.$sidebar.trigger($.Event('mobileShow.ace.sidebar'));
			else self.$sidebar.trigger($.Event('mobileHide.ace.sidebar'));
			
			$(document).triggerHandler('settings.ace', ['sidebar_collapsed' , this.minimized]);
		}
		
		
		//some helper functions
		
		//determine if we have 4th mobile style responsive sidebar and we are in mobile view
		this.is_collapsible = function() {
			var toggle
			return (this.$sidebar.hasClass('navbar-collapse'))
			&& ((toggle = $('.navbar-toggle[data-target="#'+(this.$sidebar.attr('id')||'')+'"]').get(0)) != null)
			&&  toggle.scrollHeight > 0
			//sidebar is collapsible and collapse button is visible?
		}
		//determine if we are in mobile view
		this.is_mobile_view = function() {
			var toggle
			return ((toggle = $('.menu-toggler[data-target="#'+(this.$sidebar.attr('id')||'')+'"]').get(0)) != null)
			&&  toggle.scrollHeight > 0
		}




		var submenu_working = false;
		//show submenu
		this.show = function(sub, $duration, shouldWait) {
			//'shouldWait' indicates whether to wait for previous transition (submenu toggle) to be complete or not?
			shouldWait = (shouldWait !== false);
			if(shouldWait && submenu_working) return false;
					
			var $sub = $(sub);
			var event;
			$sub.trigger(event = $.Event('show.ace.submenu'))
			if (event.isDefaultPrevented()) {
				return false;
			}
			
			if(shouldWait) submenu_working = true;


			$duration = typeof $duration !== 'undefined' ? $duration : this.settings.duration;
			
			$sub.css({
				height: 0,
				overflow: 'hidden',
				display: 'block'
			})
			.removeClass('nav-hide').addClass('nav-show')//only for window < @grid-float-breakpoint and .navbar-collapse.menu-min
			.parent().addClass('open');
			
			sub.scrollTop = 0;//this is for submenu_hover when sidebar is minimized and a submenu is scrollTop'ed using scrollbars ...

			
			var complete = function(ev, trigger) {
				ev && ev.stopPropagation();
				$sub
				.css({'transition-property': '', 'transition-duration': '', overflow:'', height: ''})
				//if(ace.vars['webkit']) ace.helper.redraw(sub);//little Chrome issue, force redraw ;)

				if(trigger !== false) $sub.trigger($.Event('shown.ace.submenu'))
				
				if(shouldWait) submenu_working = false;
			}
			
			
			var finalHeight = sub.scrollHeight;

			if($duration == 0 || finalHeight == 0 || !$.support.transition.end) {
				//(if duration is zero || element is hidden (scrollHeight == 0) || CSS3 transitions are not available)
				complete();
			}
			else {
				$sub
				.css({
					 'height': finalHeight,
					 'transition-property': 'height',
					 'transition-duration': ($duration/1000)+'s'
					}
				)
				.one($.support.transition.end, complete);
				
				//there is sometimes a glitch, so maybe retry
				if(ace.vars['android'] ) {
					setTimeout(function() {
						complete(null, false);
						ace.helper.redraw(sub);
					}, $duration + 20);
				}
			}

			return true;
		 }
		 
		 
		 //hide submenu
		 this.hide = function(sub, $duration, shouldWait) {
			//'shouldWait' indicates whether to wait for previous transition (submenu toggle) to be complete or not?
			shouldWait = (shouldWait !== false);
			if(shouldWait && submenu_working) return false;
		 
			
			var $sub = $(sub);
			var event;
			$sub.trigger(event = $.Event('hide.ace.submenu'))
			if (event.isDefaultPrevented()) {
				return false;
			}
			
			if(shouldWait) submenu_working = true;
			

			$duration = typeof $duration !== 'undefined' ? $duration : this.settings.duration;
			
			
			var initialHeight = sub.scrollHeight;
			$sub.css({
				height: initialHeight,
				overflow: 'hidden',
				display: 'block'
			})
			.parent().removeClass('open');

			sub.offsetHeight;
			//forces the "sub" to re-consider the new 'height' before transition

			
			var complete = function(ev, trigger) {
				ev && ev.stopPropagation();
				$sub
				.css({display: 'none', overflow:'', height: '', 'transition-property': '', 'transition-duration': ''})
				.removeClass('nav-show').addClass('nav-hide')//only for window < @grid-float-breakpoint and .navbar-collapse.menu-min

				if(trigger !== false) $sub.trigger($.Event('hidden.ace.submenu'))
				
				if(shouldWait) submenu_working = false;
			}
			
			
			if( $duration == 0 || initialHeight == 0 || !$.support.transition.end) {
				//(if duration is zero || element is hidden (scrollHeight == 0) || CSS3 transitions are not available)
				complete();
			}
			else {
				$sub
				.css({
					 'height': 0,
					 'transition-property': 'height',
					 'transition-duration': ($duration/1000)+'s'
					}
				)
				.one($.support.transition.end, complete);
				
				//there is sometimes a glitch, so maybe retry
				if(ace.vars['android'] ) {
					setTimeout(function() {
						complete(null, false);
						ace.helper.redraw(sub);
					}, $duration + 20);
				}
			}

			return true;
		 }

		 //toggle submenu
		 this.toggle = function(sub, $duration) {
			$duration = $duration || self.settings.duration;
		 
			if( sub.scrollHeight == 0 ) {//if an element is hidden scrollHeight becomes 0
				if( this.show(sub, $duration) ) return 1;
			} else {
				if( this.hide(sub, $duration) ) return -1;
			}
			return 0;
		 }



		//toggle mobile menu
		this.mobileToggle = function(showMenu) {
			if(this.mobile_view) {
				if(this.mobile_style == 1 || this.mobile_style == 2) {
					this.toggleMobile(typeof showMenu === 'object' ? showMenu : null, typeof showMenu === 'boolean' ? showMenu : null);
				}
				else if(this.mobile_style == 3) {
					this.toggleResponsive(typeof showMenu === 'object' ? showMenu : null, typeof showMenu === 'boolean' ? showMenu : null);
				}
				//return true;
			}
			else if(this.collapsible) {
				this.toggleCollapsible(typeof showMenu === 'object' ? showMenu : null, typeof showMenu === 'boolean' ? showMenu : null);
				//return true;
			}

			//return true;
		}
		this.mobileShow = function() {
			this.mobileToggle(true);
		}
		this.mobileHide = function() {
			this.mobileToggle(false);
		}
		
		
		this.toggleMobile = function(toggle_btn, showMenu) {
			if(!(this.mobile_style == 1 || this.mobile_style == 2)) return;
			
			var showMenu = typeof showMenu === 'boolean' ? showMenu : (typeof toggle_btn === 'boolean' ? toggle_btn : !this.$sidebar.hasClass('display'));
			
		
			if( !toggle_btn || typeof toggle_btn !== 'object' ) {
				toggle_btn = $('.menu-toggler[data-target="#'+(this.$sidebar.attr('id')||'')+'"]');
				if(toggle_btn.length != 0) toggle_btn = toggle_btn[0];
				else toggle_btn = null;
			}
			if(showMenu) {
				this.$sidebar.addClass('display');
				if(toggle_btn) $(toggle_btn).addClass('display');
			}
			else {
				this.$sidebar.removeClass('display');
				if(toggle_btn) $(toggle_btn).removeClass('display');
			}
			
			if(showMenu) self.$sidebar.trigger($.Event('mobileShow.ace.sidebar'));
			else self.$sidebar.trigger($.Event('mobileHide.ace.sidebar'));
		}
		
		
		this.toggleCollapsible = function(toggle_btn, showMenu) {
			if(this.mobile_style != 4) return;
			
			var showMenu = typeof showMenu === 'boolean' ? showMenu : (typeof toggle_btn === 'boolean' ? toggle_btn : !this.$sidebar.hasClass('in'));
			if(showMenu) {
				this.$sidebar.collapse('show');
			}
			else {
				this.$sidebar.removeClass('display');
				this.$sidebar.collapse('hide');
			}

			if(showMenu) self.$sidebar.trigger($.Event('mobileShow.ace.sidebar'));
			else self.$sidebar.trigger($.Event('mobileHide.ace.sidebar'));
		}
		
		
		
		
		
		
		////////////////
		//private functions
		//sidebar vars
		var minimized_menu_class  = 'menu-min';
		var responsive_min_class  = 'responsive-min';
		var responsive_max_class  = 'responsive-max';
		var horizontal_menu_class = 'h-sidebar';

		var sidebar_mobile_style = function() {
			//differnet mobile menu styles
			this.mobile_style = 1;//default responsive mode with toggle button inside navbar
			if(this.$sidebar.hasClass('responsive') && !$('.menu-toggler[data-target="#'+this.$sidebar.attr('id')+'"]').hasClass('navbar-toggle')) this.mobile_style = 2;//toggle button behind sidebar
			 else if(this.$sidebar.hasClass(responsive_min_class)) this.mobile_style = 3;//minimized menu
			  else if(this.$sidebar.hasClass('navbar-collapse')) this.mobile_style = 4;//collapsible (bootstrap style)
		}
		sidebar_mobile_style.call(self);
		  
		function update_vars() {
			this.mobile_view = this.mobile_style < 4 && this.is_mobile_view();
			this.collapsible = !this.mobile_view && this.is_collapsible();

			this.minimized = 
			(!this.collapsible && this.$sidebar.hasClass(minimized_menu_class))
			 ||
			(this.mobile_style == 3 && this.mobile_view && this.$sidebar.hasClass(responsive_min_class))

			this.horizontal = !(this.mobile_view || this.collapsible) && this.$sidebar.hasClass(horizontal_menu_class)
		}

		//update some basic variables
		$(window).on('resize.sidebar.vars' , function(){
			update_vars.call(self);
		}).triggerHandler('resize.sidebar.vars')
		
		
		//toggling (show/hide) submenu elements
		this.$sidebar.on(ace.click_event+'.ace.submenu', '.nav-list', function (ev) {
			var nav_list = this;

			//check to see if we have clicked on an element which is inside a .dropdown-toggle element?!
			//if so, it means we should toggle a submenu
			var link_element = $(ev.target).closest('a');
			if(!link_element || link_element.length == 0) return;//return if not clicked inside a link element

			var minimized  = self.minimized && !self.collapsible;
			//if .sidebar is .navbar-collapse and in small device mode, then let minimized be uneffective
	
			if( !link_element.hasClass('dropdown-toggle') ) {//it doesn't have a submenu return
				//just one thing before we return
				//if sidebar is collapsed(minimized) and we click on a first level menu item
				//and the click is on the icon, not on the menu text then let's cancel event and cancel navigation
				//Good for touch devices, that when the icon is tapped to see the menu text, navigation is cancelled
				//navigation is only done when menu text is tapped

				if( ace.click_event == 'tap'
					&&
					minimized
					&&
					link_element.get(0).parentNode.parentNode == nav_list )//only level-1 links
				{
					var text = link_element.find('.menu-text').get(0);
					if( text != null && ev.target != text && !$.contains(text , ev.target) ) {//not clicking on the text or its children
						ev.preventDefault();
						return false;
					}
				}


				//ios safari only has a bit of a problem not navigating to link address when scrolling down
				//specify data-link attribute to ignore this
				if(ace.vars['ios_safari'] && link_element.attr('data-link') !== 'false') {
					//only ios safari has a bit of a problem not navigating to link address when scrolling down
					//please see issues section in documentation
					document.location = link_element.attr('href');
					ev.preventDefault();
					return false;
				}

				return;
			}
			
			ev.preventDefault();
			
			


			var sub = link_element.siblings('.submenu').get(0);
			if(!sub) return false;
			var $sub = $(sub);

			var height_change = 0;//the amount of height change in .nav-list

			var parent_ul = sub.parentNode.parentNode;
			if
			(
				( minimized && parent_ul == nav_list )
				 || 
				( ( $sub.parent().hasClass('hover') && $sub.css('position') == 'absolute' ) && !self.collapsible )
			)
			{
				return false;
			}

			
			var sub_hidden = (sub.scrollHeight == 0)

			//if not open and visible, let's open it and make it visible
			if( sub_hidden && self.settings.hide_open_subs ) {//being shown now
			  $(parent_ul).find('> .open > .submenu').each(function() {
				//close all other open submenus except for the active one
				if(this != sub && !$(this.parentNode).hasClass('active')) {
					height_change -= this.scrollHeight;
					self.hide(this, self.settings.duration, false);
				}
			  })
			}

			if( sub_hidden ) {//being shown now
				self.show(sub, self.settings.duration);
				//if a submenu is being shown and another one previously started to hide, then we may need to update/hide scrollbars
				//but if no previous submenu is being hidden, then no need to check if we need to hide the scrollbars in advance
				if(height_change != 0) height_change += sub.scrollHeight;//we need new updated 'scrollHeight' here
			} else {
				self.hide(sub, self.settings.duration);
				height_change -= sub.scrollHeight;
				//== -1 means submenu is being hidden
			}

			//hide scrollbars if content is going to be small enough that scrollbars is not needed anymore
			//do this almost before submenu hiding begins
			//but when minimized submenu's toggle should have no effect
			if (height_change != 0) {
				if(self.$sidebar.attr('data-sidebar-scroll') == 'true' && !self.minimized) 
					self.$sidebar.ace_sidebar_scroll('prehide', height_change)
			}

			return false;
		});
		
		
	}//end of Sidebar
	

	//sidebar events
	
	//menu-toggler
	$(document)
	.on(ace.click_event+'.ace.menu', '.menu-toggler', function(e){
		var btn = $(this);
		var sidebar = $(btn.attr('data-target'));
		if(sidebar.length == 0) return;
		
		e.preventDefault();
				
		//sidebar.toggleClass('display');
		//btn.toggleClass('display');
		
		sidebar.ace_sidebar('mobileToggle', this);
		
		var click_event = ace.click_event+'.ace.autohide';
		var auto_hide = sidebar.attr('data-auto-hide') === 'true';

		if( btn.hasClass('display') ) {
			//hide menu if clicked outside of it!
			if(auto_hide) {
				$(document).on(click_event, function(ev) {
					if( sidebar.get(0) == ev.target || $.contains(sidebar.get(0), ev.target) ) {
						ev.stopPropagation();
						return;
					}

					sidebar.ace_sidebar('mobileToggle', this, false);
					$(document).off(click_event);
				})
			}

			if(sidebar.attr('data-sidebar-scroll') == 'true') sidebar.ace_sidebar_scroll('reset');
		}
		else {
			if(auto_hide) $(document).off(click_event);
		}

		return false;
	})
	//sidebar collapse/expand button
	.on(ace.click_event+'.ace.menu', '.sidebar-collapse', function(e){
		
		var target = $(this).attr('data-target'), $sidebar = null;
		if(target) $sidebar = $(target);
		if($sidebar == null || $sidebar.length == 0) $sidebar = $(this).closest('.sidebar');
		if($sidebar.length == 0) return;

		e.preventDefault();
		$sidebar.ace_sidebar('toggleMenu', this);
	})
	//this button is used in `mobile_style = 3` responsive menu style to expand minimized sidebar
	.on(ace.click_event+'.ace.menu', '.sidebar-expand', function(e){
		var target = $(this).attr('data-target'), $sidebar = null;
		if(target) $sidebar = $(target);
		if($sidebar == null || $sidebar.length == 0) $sidebar = $(this).closest('.sidebar');
		if($sidebar.length == 0) return;	
	
		var btn = this;
		e.preventDefault();
		$sidebar.ace_sidebar('toggleResponsive', this);
		
		var click_event = ace.click_event+'.ace.autohide';
		if($sidebar.attr('data-auto-hide') === 'true') {
			if( $sidebar.hasClass(responsive_max_class) ) {
				$(document).on(click_event, function(ev) {
					if( $sidebar.get(0) == ev.target || $.contains($sidebar.get(0), ev.target) ) {
						ev.stopPropagation();
						return;
					}

					$sidebar.ace_sidebar('toggleResponsive', btn);
					$(document).off(click_event);
				})
			}
			else {
				$(document).off(click_event);
			}
		}
	})

	
	$.fn.ace_sidebar = function (option, value, value2) {
		var method_call;

		var $set = this.each(function () {
			var $this = $(this);
			var data = $this.data('ace_sidebar');
			var options = typeof option === 'object' && option;

			if (!data) $this.data('ace_sidebar', (data = new Sidebar(this, options)));
			if (typeof option === 'string' && typeof data[option] === 'function') {
				if(value instanceof Array) method_call = data[option].apply(data, value);
				else if(value2 !== undefined) method_call = data[option](value, value2);
				else method_call = data[option](value);
			}
		});

		return (method_call === undefined) ? $set : method_call;
	};
	
	
	$.fn.ace_sidebar.defaults = {
		'duration': 300,
		'hide_open_subs': true
    }


})(window.jQuery);


/**
 <b>Widget boxes</b>
*/
(function($ , undefined) {

	var Widget_Box = function(box, options) {
		this.$box = $(box);
		var that = this;
		//this.options = $.extend({}, $.fn.widget_box.defaults, options);

		this.reload = function() {
			var $box = this.$box;
			var $remove_position = false;
			if($box.css('position') == 'static') {
				$remove_position = true;
				$box.addClass('position-relative');
			}
			$box.append('<div class="widget-box-overlay"><i class="'+ ace.vars['icon'] + 'loading-icon fa fa-spinner fa-spin fa-2x white"></i></div>');

			$box.one('reloaded.ace.widget', function() {
				$box.find('.widget-box-overlay').remove();
				if($remove_position) $box.removeClass('position-relative');
			});
		}

		this.closeFast = function() {
			this.close(0);
		}
		this.close = function(closeSpeed) {
			var $box = this.$box;
			var closeSpeed   = typeof closeSpeed === 'undefined' ? 300 : closeSpeed;
			$box.fadeOut(closeSpeed , function(){
					$box.trigger('closed.ace.widget');
					$box.remove();
				}
			)
		}
		
		this.toggleFast = function() {
			this.toggle(null, null, 0, 0);
		}
		
		this.toggle = function(type, button, expandSpeed, collapseSpeed) {
			var $box = this.$box;
			var $body = $box.find('.widget-body').eq(0);
			var $icon = null;
			
			var event_name = type || ($box.hasClass('collapsed') ? 'show' : 'hide');
			var event_complete_name = event_name == 'show' ? 'shown' : 'hidden';

			if( !button ) {
				button = $box.find('> .widget-header a[data-action=collapse]').eq(0);
				if(button.length == 0) button = null;
			}

			if(button) {
			
				$icon = button.find(ace.vars['.icon']).eq(0);

				var $match
				var $icon_down = null
				var $icon_up = null
				if( ($icon_down = $icon.attr('data-icon-show')) ) {
					$icon_up = $icon.attr('data-icon-hide')
				}
				else if( $match = $icon.attr('class').match(/fa\-(.*)\-(up|down)/) ) {
					$icon_down = 'fa-'+$match[1]+'-down'
					$icon_up = 'fa-'+$match[1]+'-up'
				}
			}

			var expandSpeed   = typeof expandSpeed === 'undefined' ? 250 : expandSpeed;
			var collapseSpeed = typeof collapseSpeed === 'undefined' ? 200 : collapseSpeed;
			

			if( event_name == 'show' ) {
				if($icon) $icon.removeClass($icon_down).addClass($icon_up);

				$body.hide();
				$box.removeClass('collapsed');
				$body.slideDown(expandSpeed, function(){
					$box.trigger(event_complete_name+'.ace.widget')
				})
			}
			else {
				if($icon) $icon.removeClass($icon_up).addClass($icon_down);
				$body.slideUp(collapseSpeed, function(){
						$box.addClass('collapsed')
						$box.trigger(event_complete_name+'.ace.widget')
					}
				);
			}
			
			$box.trigger('toggled.ace.widget', [event_name]);
		}
		
		this.hide = function() {
			this.toggle('hide');
		}
		this.show = function() {
			this.toggle('show');
		}
		
		this.hideFast = function() {
			this.toggle('hide', null, 0, 0);
		}
		this.showFast = function() {
			this.toggle('show', null, 0, 0);
		}
		
		
		this.fullscreen = function(makeFullscreen) {
			var $icon = this.$box.find('> .widget-header a[data-action=fullscreen]').find(ace.vars['.icon']).eq(0);
			var $icon_expand = null
			var $icon_compress = null
			if( ($icon_expand = $icon.attr('data-icon1')) ) {
				$icon_compress = $icon.attr('data-icon2')
			}
			else {
				$icon_expand = 'fa-expand';
				$icon_compress = 'fa-compress';
			}
			
			
			var isAlreadyFull = this.$box.hasClass('fullscreen');
			var noMakeFullscreenParam = makeFullscreen !== true && makeFullscreen !== false;//no true/false arguement provided for this function, so decide based on widget box classnames
			
			//make it fullscreen if:
			//1)we want to go full screen anyway
			//2)it is not fullscreen already
			if( makeFullscreen === true || (noMakeFullscreenParam && !isAlreadyFull) ) {
				$icon.removeClass($icon_expand).addClass($icon_compress);
				this.$box.addClass('fullscreen');
				
				applyScrollbars(this.$box, true);
			}
			else if(makeFullscreen === false || (noMakeFullscreenParam && isAlreadyFull) ) {
				$icon.addClass($icon_expand).removeClass($icon_compress);
				this.$box.removeClass('fullscreen');
				
				applyScrollbars(this.$box, false);
			}
			
			this.$box.trigger('fullscreened.ace.widget')
		}

	}
	
	$.fn.widget_box = function (option, value) {
		var method_call;

		var $set = this.each(function () {
			var $this = $(this);
			var data = $this.data('widget_box');
			var options = typeof option === 'object' && option;

			if (!data) $this.data('widget_box', (data = new Widget_Box(this, options)));
			if (typeof option === 'string') method_call = data[option](value);
		});

		return (method_call === undefined) ? $set : method_call;
	};


	$(document).on(ace['click_event']+'.ace.widget', '.widget-header a[data-action]', function (ev) {
		ev.preventDefault();

		var $this = $(this);
		var $box = $this.closest('.widget-box');
		if( $box.length == 0 || $box.hasClass('ui-sortable-helper') ) return;

		var $widget_box = $box.data('widget_box');
		if (!$widget_box) {
			$box.data('widget_box', ($widget_box = new Widget_Box($box.get(0))));
		}

		var $action = $this.data('action');
		if($action == 'collapse') {
			var event_name = $box.hasClass('collapsed') ? 'show' : 'hide';

			var event
			$box.trigger(event = $.Event(event_name+'.ace.widget'))
			if (event.isDefaultPrevented()) return
			
			$box.trigger(event = $.Event('toggle.ace.widget'), [event_name]);

			$widget_box.toggle(event_name, $this);
		}
		else if($action == 'close') {
			var event
			$box.trigger(event = $.Event('close.ace.widget'))
			if (event.isDefaultPrevented()) return

			$widget_box.close();
		}
		else if($action == 'reload') {
			$this.blur();
			var event
			$box.trigger(event = $.Event('reload.ace.widget'))
			if (event.isDefaultPrevented()) return

			$widget_box.reload();
		}
		else if($action == 'fullscreen') {
			var event
			$box.trigger(event = $.Event('fullscreen.ace.widget'))
			if (event.isDefaultPrevented()) return
		
			$widget_box.fullscreen();
		}
		else if($action == 'settings') {
			$box.trigger('setting.ace.widget')
		}

	});
	
		
	function applyScrollbars($widget, enable) {
		var $main = $widget.find('.widget-main').eq(0);
		$(window).off('resize.widget.scroll');
		
		//IE8 has an unresolvable issue!!! re-scrollbaring with unknown values?!
		var nativeScrollbars = ace.vars['old_ie'] || ace.vars['touch'];
		
		if(enable) {
			var ace_scroll = $main.data('ace_scroll');
			if( ace_scroll ) {
				$main.data('save_scroll', {size: ace_scroll['size'], lock: ace_scroll['lock'], lock_anyway: ace_scroll['lock_anyway']});
			}
			
			var size = $widget.height() - $widget.find('.widget-header').height() - 10;//extra paddings
			size = parseInt(size);
			
			$main.css('min-height', size);
			if( !nativeScrollbars ) {
				if( ace_scroll ) {
					$main.ace_scroll('update', {'size': size, 'mouseWheelLock': true, 'lockAnyway': true});
				}
				else {
					$main.ace_scroll({'size': size, 'mouseWheelLock': true, 'lockAnyway': true});
				}
				$main.ace_scroll('enable').ace_scroll('reset');
			}
			else {
				if( ace_scroll ) $main.ace_scroll('disable');
				$main.css('max-height', size).addClass('overflow-scroll');
			}
			
			
			$(window)
			.on('resize.widget.scroll', function() {
				var size = $widget.height() - $widget.find('.widget-header').height() - 10;//extra paddings
				size = parseInt(size);
				
				$main.css('min-height', size);
				if( !nativeScrollbars ) {
					$main.ace_scroll('update', {'size': size}).ace_scroll('reset');
				}
				else {
					$main.css('max-height', size).addClass('overflow-scroll');
				}
			});
		}
		
		else  {
			$main.css('min-height', '');
			var saved_scroll = $main.data('save_scroll');
			if(saved_scroll) {
				$main
				.ace_scroll('update', {'size': saved_scroll['size'], 'mouseWheelLock': saved_scroll['lock'], 'lockAnyway': saved_scroll['lock_anyway']})
				.ace_scroll('enable')
				.ace_scroll('reset');
			}
			
			if( !nativeScrollbars ) {				
				if(!saved_scroll) $main.ace_scroll('disable');				
			}
			else {
				$main.css('max-height', '').removeClass('overflow-scroll');
			}
		}
	}

})(window.jQuery);

/**
 <b>Auto content padding on fixed navbar &amp; breadcrumbs</b>.
 Navbar's content and height is often predictable and when navbar is fixed we can add appropriate padding to content area using CSS.
 
 But when navbar is fixed and its content size and height is not predictable we may need to add the necessary padding to content area dynamically using Javascript.

 It's not often needed and you can have good results using CSS3 media queries to add necessary paddings based on window size.
*/
(function($ , undefined) {

	var navbar = $('.navbar').eq(0);
	var navbar_container = $('.navbar-container').eq(0);
	
	var sidebar = $('.sidebar').eq(0);
	
	var main_container = $('.main-container').get(0);
	
	var breadcrumbs = $('.breadcrumbs').eq(0);
	var page_content = $('.page-content').get(0);
	
	var default_padding = 8

	if(navbar.length > 0) {
	  $(window).on('resize.auto_padding', function() {
		if( navbar.css('position') == 'fixed' ) {
			var padding1 = !ace.vars['nav_collapse'] ? navbar.outerHeight() : navbar_container.outerHeight();
			padding1 = parseInt(padding1);
			main_container.style.paddingTop = padding1 + 'px';
			
			if(ace.vars['non_auto_fixed'] && sidebar.length > 0) {
				if(sidebar.css('position') == 'fixed') {
					sidebar.get(0).style.top = padding1 + 'px';
				}
				else sidebar.get(0).style.top = '';
			}

			if( breadcrumbs.length > 0 ) {
				if(breadcrumbs.css('position') == 'fixed') {
					var padding2 = default_padding + breadcrumbs.outerHeight() + parseInt(breadcrumbs.css('margin-top'));
					padding2 = parseInt(padding2);
					page_content.style['paddingTop'] = padding2 + 'px';

					if(ace.vars['non_auto_fixed']) breadcrumbs.get(0).style.top = padding1 + 'px';
				} else {
					page_content.style.paddingTop = '';
					if(ace.vars['non_auto_fixed']) breadcrumbs.get(0).style.top = '';
				}
			}
		}
		else {
			main_container.style.paddingTop = '';
			page_content.style.paddingTop = '';
			
			if(ace.vars['non_auto_fixed']) {
				if(sidebar.length > 0) {
					sidebar.get(0).style.top = '';
				}
				if(breadcrumbs.length > 0) {
					breadcrumbs.get(0).style.top = '';
				}
			}
		}
	  }).triggerHandler('resize.auto_padding');

	  $(document).on('settings.ace.auto_padding', function(ev, event_name, event_val) {
		if(event_name == 'navbar_fixed' || event_name == 'breadcrumbs_fixed') {
			if(ace.vars['webkit']) {
				//force new 'css position' values to kick in
				navbar.get(0).offsetHeight;
				if(breadcrumbs.length > 0) breadcrumbs.get(0).offsetHeight;
			}
			$(window).triggerHandler('resize.auto_padding');
		}
	  });
	  
	  /**$('#skin-colorpicker').on('change', function() {
		$(window).triggerHandler('resize.auto_padding');
	  });*/
	}

})(window.jQuery);

/**
 <b>Auto Container</b> Adds .container when window size is above 1140px.
 In Bootstrap you should stick with fixed width breakpoints.
 You can use this feature to enable fixed container only when window size is above 1140px
*/
(function($ , undefined) {

 $(window).on('resize.auto_container', function() {
	var enable = $(window).width() > 1140;
	try {
		ace.settings.main_container_fixed(enable, false, false);
	} catch(e) {
		if(enable) $('.main-container,.navbar-container').addClass('container');
		else $('.main-container,.navbar-container').removeClass('container');
		$(document).trigger('settings.ace', ['main_container_fixed' , enable]);
	}
 }).triggerHandler('resize.auto_container');

})(window.jQuery);

