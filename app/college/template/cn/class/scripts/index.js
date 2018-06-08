var swiper = new Swiper('.swiper-container', {
	pagination: '.swiper-pagination',
	slidesPerView: 3,
	paginationClickable: true,
	spaceBetween: 30
});
function go(id){
	var url = '/class/index?page=' + id + '&state=' + state + '&sort=' + sort;
	window.location.href = url;
}

//报名状态
$("input[name='enroll']").click(function(){
	var state = $(this).val();
	var url = '/class/index?state=' + state + '&bid=' + bid;
	window.location.href = url;
});

//排序
$('.sort li').each(function(){
	var id = $(this).attr('id');
	if(id == sort){
		$(this).addClass('active');
	}else {
		$(this).removeClass('active');
	}
});

//班级列表
$('.classList').click(function(){
	var url = $(this).data('url');
	window.location.href = url;
});

//分院列表
$('.branchList li').each(function(){
	var id  = $(this).attr('id');
	if(id == bid){
		$(this).addClass('active');
	}else {
		$(this).removeClass('active');
	}
});