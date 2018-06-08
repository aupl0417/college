var page_setting = {
	'title': ''
};
var currentMenuId = typeof currentMenuId == 'undefined' ? '5': currentMenuId;
function init_Menu_Breadcrumbs_Message_Info(currentMenuId) {
	var root = currentMenuId.substr(0, 1);
	$.ajax({
		'dataType': 'JSON',
		'url': '/public/load.json',
		'data': {
			currentMenuId: currentMenuId
		},
		'async': false,
		'success': function(data) {

			if (data.id == '1001') {

				/* 加载菜单栏 */
				var topMenu = '',
				sidebar = '';

				var menuData = data.info.menu; //菜单数据
				for (var i = 0, l = menuData.menu.length; i < l; i++) {
					var open = menuData.menu[i].id == root ? ' open active ': '';
					var url = menuData.menu[i].url;
					var target = (menuData.menu[i].target == 1) ? ' target="_blank"': '';
					topMenu += '<li class="dtnavli' + open + '"><a href="' + url + '"' + target + ' class="dtnava dropdown-toggle">' + menuData.menu[i].name + '</a></li>';
					if (menuData.menu[i].id == root) { //当前站点						
						var sidebarData = menuData.menu[i].children;

						for (var k = 0, len = sidebarData.length; k < len; k++) {
							if (sidebarData[k].children) { //有子菜单									
								var children = sidebarData[k].children;
								submenu = '<ul class="submenu">';
								var parentActive = false;
								for (var j = 0, jen = children.length; j < jen; j++) {
									parentActive = (!parentActive && children[j].id == currentMenuId) ? true: parentActive;
									var active = children[j].id == currentMenuId ? " active": '';
									var iconCircle = children[j].id == currentMenuId ? "menu-icon fa fa-caret-right": 'menu-icon fa fa-caret-right';
									submenu += '<li id="menu_' + children[j].id + '" class="' + active + '"><a href="#' + children[j].url + '" class="navinbg"><i class="' + iconCircle + '"></i>' + children[j].name + '</a><b class="arrow"></b></li>';
								}
								submenu += '</ul>';
								var active = parentActive ? ' active open': '';
								sidebar += '<li id="menu_' + sidebarData[k].id + '" class="highlight noborder' + active + '"><a href="' + sidebarData[k].url + '" class="dropdown-toggle navlistCon"><i class="menu-icon fa fa-list hide"></i><span class="menu-text">' + sidebarData[k].name + '</span><b class="arrow fa fa-angle-down"></b></a><b class="arrow"></b>' + submenu + '</li>';
							} else { //没有子菜单
								if (sidebarData[k].id == currentMenuId || (sidebarData[k].id.indexOf(currentMenuId) === 0 && currentMenuId > 10)) {
									var active = " active ";
								} else {
									var active = '';
								}
								sidebar += '<li id="menu_' + sidebarData[k].id + '" class="highlight noborder' + active + '"><a href="#' + sidebarData[k].url + '" class="navlistCon"><i class="menu-icon fa fa-file-o hide"></i><span class="menu-text">' + sidebarData[k].name + '</span></a><b class="arrow"></b></li>';
							}

						}
					}
				}
				/* 顶部菜单栏 */
				$('#topMenu').html($(topMenu));
				/* 左侧菜单栏 */
				$('#sidebar .nav-list').html($(sidebar));

				/* 面包屑导航栏 */
				$('#breadcrumbs .breadcrumb').html(data.info.breadcrumb);
				page_setting.title = $('#breadcrumbs .breadcrumb li:last').html();
				/* 站内信列表 */

				//未读站内信显示
				unread(data.info.messageList);

				/* 用户信息 */
				$('a#loginInfo img.nav-user-photo').attr('src', data.info.userInfo.logo);
				$('a#loginInfo span.user-info').html(data.info.userInfo.nick);

			} else {

			}
		}
	});
}

/* --------------------------------------------------------------------------*/
/**
* @未读站内信  
* @Param message  array
* @Returns void 
*/
/* ----------------------------------------------------------------------------*/
function unread(message) {
	var unread_msg = '';
	if (message && message.length > 0) {
		var num = message.length;
		$('#header_notification_bar').find("span.badge-success").html(num).removeClass('hidden');
		$('#unread_num').html(num);

		var pay_url = '';
		for (var i = 0, l = message.length; i < l; i++) {
			if (i == 0) {
				pay_url = message[i]['url'] + '/#/sitemsg';
			}
			unread_msg += '<li> <a href="javascript:;" onclick="javascript:Global.viewSiteMsg(' + message[i]['mbr_mbID'] + ');get_unrear_msg('+ message[i]['mbr_mbID'] +');" class="clearfix" > <span class="msg-body dtmailcon"> <span class="msg-title dtemail"><span class="blue">' + '</span>' + message[i]['mb_title'] + '</span><span class="msg-time">  ' + message[i]['mbr_getTime'] + ' </span></span> </a> </li>';
		}
	}else {
		$('#header_notification_bar').find("span.badge-success").html('');
		$('#unread_num').html('');
	}
	$('#unreadList').html(unread_msg);
}

function get_unrear_msg(id) {
	$.ajax({
		url: "/public/unread.json",
		data: {
      'id' : id,
    },
		type: "get",
		cache: false,
		dataType: 'json',
		success: function(message) {
			message = message.info;
			unread(message);
		}
	});
}
init_Menu_Breadcrumbs_Message_Info(currentMenuId);

