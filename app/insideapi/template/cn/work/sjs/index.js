/*tab轮换jq代码*/

$(function () {
	$('#qb-tab').children().click(function () {
		$('#qb-tab').children().removeClass("active");
		$(this).addClass('active');
		$(".qb-content > div").hide().eq($('#qb-tab li').index(this)).show();
	});
});

/*tab轮换jq代码*/

/*选项卡万能插件*/
$(function () {

	$('.nTab').each(function (tab_i, tab_e) {
		$('.nav > li', this).each(function (nav_i, nav_e) {
			$(this).on('click', function () {
				$this = $(this);
				$this.parent('ul').find('li').removeClass();
				$this.addClass('active');
				var showid = $(this).data('target');
				$(".TabContent > .tcont:not([id='" + showid + "'])", $(tab_e)).hide();
				$(".TabContent > .tcont#" + showid + "", $(tab_e)).fadeIn(200).stop(true, true);
				//.sibings().hide();
			});
		});
	});
	//Index.init();

});
/*选项卡万能插件*/

/*伸缩*/

$(function () {
	$('h4.cap').each(function (h_i, h_e) {
		$(this).on('click', function () {

			var expland = $(this).data('expland');
			if (expland) {
				$('#' + $(this).data('target')).stop(true, true).slideUp(200);
				$(this).data('expland', false);
			} else {
				$('#' + $(this).data('target')).stop(true, true).slideDown(200);
				$(this).data('expland', true);
			}
		});
	});

});

/*伸缩*/
