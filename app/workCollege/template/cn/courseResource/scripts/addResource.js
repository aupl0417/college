$('#transfer_detail').handleForm({
	rules: {
		'name': {
			required: true,
		},
		'courseId': {
			required: true,
		},
		'url': {
			required: true,
		},
		
	},
	messages: {
		'name': {
			required: "请填写教师等级名称",
		},
		'courseId': {
			required: "请选择所属课程",
		},
		'url': {
			required: "请添加文件或文件地址",
		}
	},
	closest: '.form-group',
},
function(data, statusText) {
	var modalOptions = {
		"container": "#formCourse",
		"place": "prepend",
		"type": "warning",
		"message": '',
		"close": true,
		"reset": true,
		"focus": true,
		"closeInSeconds": "0",
		"icon": "warning"
	};
	modalOptions.message = data.info || data.msg;
	if (data.id == '1001') {
		bootbox.alert(data.msg, function() {
			$('#temp-modal-power').modal('hide');
			Grid.getDataTable().ajax.reload(null, false);
		});
	} else {
		Global.alert(modalOptions);
	}
});

var flowModalUpload = function() {
	$('.modalUpload').each(function(i) {
		console.log(i);
		$(this).handleUpload(function(data) {
			if (data.status == 'success') {
				var fileinput = $(".modalUpload:eq(" + i + ")").prev('.fileinput');
				$("input[type='hidden']", fileinput).val(data.savename);
				$("input[name='files']").val(data.savename);
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
	var html = '<div class="row" style="margin-top:10px;margin-left:2px;">' + buttonString + '</div>';
	$('.resource').append(html);
});

function delButton(obj){
	$(obj).parent().remove();
}

$('.addFile').on('click', function(){
	var html = '<div class="row upFile" style="margin-top:10px;">';
    html += $('.resource').children('div').eq(1).html();
	html += '</div>';
	$('.resource').append(html);
	$('.upFileButton').click();
});


