var swiper = new Swiper('.swiper-container', {
	pagination: '.swiper-pagination',
	slidesPerView: 3,
	paginationClickable: true,
	spaceBetween: 30
});
$("#appDateTime").datetimepicker({
	language:  'cn',
	weekStart: 1,
	todayBtn:  1,
	autoclose: 1,
	todayHighlight: 1,
	startView: 2,
	forceParse: 0,
	showMeridian: 1

});

$(function(){
	$('.bg_ee li').eq(0).addClass('active'); 
	$("#click-popup").click(function () {
		$("#enroll").show();
		$(".zhez-popoup").show();
		$("body,html").addClass("over");
	});
	var carService = 0;
	$("input[name='carService']").click(function(){
		if($(this).val() == 1){
			$(this).attr('checked', true);
			$('#no').attr('checked', false);
			carService = 1;
			$(".radio-cont").show();
			var top =$(".popoup-details").height()/2
			$(".popoup-details").css({
				"margin-top":-top
			})
		}else{
			$(this).attr('checked', true);
			$('#yes').attr('checked', false);
			carService = 0;
			$(".radio-cont").hide();
			var top =$(".popoup-details").height()/2
			$(".popoup-details").css({
				"margin-top":-top
			})
		}
	});

	var counts = '';
	var station = '';
	$("#station").change(function () {
		station = $(this).children('option:selected').val();
	});
	$("#counts").change(function () {
		counts = $(this).children('option:selected').val();
	});

	//班级列表
	$('.classList').click(function(){
		var url = $(this).data('url');
		window.location.href = url;
	});

	$('#enrollSub').click(function(){
		var url     = '/public/dealEnroll.json';
		var appDateTime = $("#appDateTime").val();
		var province = $("select[name='province']").val();
		var data = {};

		if(!province){
			layer.alert('请选择所属区域！');
			return false;
		}

		if(carService == 1){
			data = {arrivalTime:appDateTime, station:station, counts:counts,classId : classId, carService:carService,province:province};
		}else {
			data = {classId : classId, carService:carService,province:province};
		}

		$.ajax({
			data: data,
			type:'post',
			url:url,
			dataType:'json',
			success:function(data){
				if(data.id == '1001'){
					$('#success').show();
					$('.enroll').attr('disabled', true);
					var enrollCount = $('.enrollCount').text();
					enrollCount = parseInt(enrollCount) + 1;
					$('.enrollCount').text(enrollCount);
					var url = '/student/order';//报名记录
					$('#success').find('.url').attr('href', url);
				}else if(data.id == '1005') {
					$('#fail_level').show();
					var url = 'https://u.dttx.com/#/upgrade';//升级会员
					$('#fail_level').find('.url').attr('href', url);
				}else if(data.id == '1006'){
					$('#fail_auth').show();
					var url = 'https://u.dttx.com/#/auth';//会员实名认证
					$('#fail_auth').find('.url').attr('href', url);
				}else {
					$('.msg').text(data.info);
					$('#common').show();
					var url = '/student/classList'; //查看班级
					$('#common').find('.url').attr('href', url);
				}
				$('#enroll').hide();
				$(".zhez-popoup").show();
				$("body,html").addClass("over");
			}
		});
	});

	$("#cancel-popup").click(function () {
		$(".popoup-details").hide();
		$(".zhez-popoup").hide();
		$("body,html").removeClass("over");
	})
	$(".glyphicon-remove").click(function () {
		$(".popoup-details").hide();
		$(".zhez-popoup").hide();
		$("body,html").removeClass("over");
	})


	if(enrollState == 1){
		$('#click-popup').attr('disabled', true);
	}else {
		$('#cancel-enroll').hide();
	}

	$('#cancel-enroll').click(function(){
		layer.confirm('确定要取消报名？', {
			btn: ['确定','取消'] //按钮
		}, function(){
			//layer.msg('的确很重要', {icon: 1});
			var url     = '/public/delEnroll.json';
			console.log(classId);
			$.ajax({
				data: {classId:classId},
				type:'post',
				url:url,
				dataType:'json',
				success:function(data){
					layer.alert(data.info);
					setInterval(function(){
						window.location.reload();
					}, 2000);
				}
			});
		});
		//var url     = '/public/delEnroll.json';
		//console.log(classId);
		//$.ajax({
		//	data: {classId:classId},
		//	type:'post',
		//	url:url,
		//	dataType:'json',
		//	success:function(data){
		//		layer.alert(data.info);
		//		setInterval(function(){
		//			window.location.reload();
		//		}, 2000);
		//	}
		//});
	});
	
	var level = '5';//如果不选择星级，则默认星级为5
	$('.commentLevel').find('i').each(function(){
		$(this).click(function(){
			$(this).prevAll().addClass('c-yellow');
			$(this).addClass('c-yellow');
			$(this).nextAll().removeClass('c-yellow');
			level = $(this).attr('index');
		});
	});
	
	$('#submit').click(function(){
		var id      = $('input[name=id]').val();
		var content = $('textarea[name=content]').val();
		var url     = '/public/comment.json';

		if(content == ''){
			layer.alert('请输入评论内容');
		}

		$.ajax({
			data:{
				'id'      : id,
				'content' : content,
				'level'   : level
			},
			type:'post',
			url:url,
			dataType:'json',
			success:function(data){
				console.log(data);
				if(data.id == '1001'){
					layer.alert('发布成功');
					var html = '';
					html += '<div class="pb20 pt20 dashed-b">';
					html +=     '<div class="row mb5">';
					html +=     '<div class="col-xs-1 text-center pr0"><img src="' + data.info.avatar + '" width="29px;" height="29px;" /></div>';
					html +=     '<div class="col-xs-9 mt5">' + data.info.content + '</div>';
					html +=     '<div class="col-xs-2 text-right mt5">' + data.info.createTime + '</div>';
					html +=     '</div>';
					html +=     '<div class="row">'
					html +=     '<div class="col-xs-1 text-center pr0">' + data.info.username + '</div>';
					html +=     '</div>';
					html +=     '</div>';
					$('.commentList').prepend(html);
					$('textarea[name=content]').val('');
					var $obj = $('.commentCount');
					var count = parseInt($obj.text()) + 1;
					$obj.text(count + '人评价');
				}else {
					layer.alert(data.info);
				}
			}
		});
	});
	
	$('#collect').on('click', function(){
		var url = '/class/collect.json';
		$.ajax({
			data:{classId : classId},
			type:'post',
			url:url,
			dataType:'json',
			success:function(data){
				if(data.id == '1001'){
					var src = $('#collect').children('img').attr('src');
					$('#collect').children('img').attr('src', data.info.imgUrl);
				}else {
					layer.alert(data.info);
				}
			}
		});
	});
	
	

});