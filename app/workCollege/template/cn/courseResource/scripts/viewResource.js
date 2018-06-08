
var flowModalUpload = function() {
	$('.modalUpload').each(function(i) {
		$(this).handleUpload(function(data) {
			if (data.status == 'success') {
				var fileinput = $(".modalUpload:eq(" + i + ")").prev('.fileinput');
				$("input[type='hidden']", fileinput).val(data.savename);
				$('.thumbnail img', fileinput).prop('src', data.filename + '?r=' + Math.random());
				$('.thumbnail a.fancybox-button', fileinput).prop('href', data.filename + '?r=' + Math.random());
			} else {
				return false;
			}
		});
	});
};

flowModalUpload();

$('.button').click(function(){
	var html = '<div class="row" style="margin-top:10px;">' + buttonString + '</div>';
	$('.resource').append(html);
});

function delButton(obj){
	$(obj).parent().remove();
}


